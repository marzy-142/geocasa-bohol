<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Conversation;
use App\Notifications\NewInquiryNotification;
use App\Events\NewInquiryReceived;
use App\Services\BrokerRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PublicController extends Controller
{
    protected $brokerRankingService;

    public function __construct(BrokerRankingService $brokerRankingService)
    {
        $this->brokerRankingService = $brokerRankingService;
    }

    /**
     * Get all property types (predefined + custom) for filtering
     */
    private function getAllPropertyTypes()
    {
        // Get predefined types with counts (only available properties for public)
        $predefinedTypes = collect(Property::TYPES)->map(function($type) {
            $count = Property::where('status', 'available')
                ->whereJsonContains('types', $type)
                ->count();
            return [
                'value' => $type,
                'label' => Property::formatPropertyType($type),
                'count' => $count
            ];
        })->filter(fn($t) => $t['count'] > 0);

        // Get custom types (only from available properties)
        $customTypes = Property::where('status', 'available')
            ->whereJsonContains('types', 'other')
            ->whereNotNull('custom_type_text')
            ->select('custom_type_text')
            ->distinct()
            ->get()
            ->map(function($item) {
                $count = Property::where('status', 'available')
                    ->where('custom_type_text', $item->custom_type_text)
                    ->count();
                return [
                    'value' => 'custom:' . $item->custom_type_text,
                    'label' => $item->custom_type_text,
                    'count' => $count
                ];
            });

        return $predefinedTypes->concat($customTypes)
            ->sortBy('label')
            ->values()
            ->toArray();
    }

    /**
     * Display the home page with featured properties and stats
     */
    public function home()
    {
        // Get featured properties (limit to 6)
        // Use publicly visible statuses (available, pending, reserved, under_negotiation)
        $featuredProperties = Property::with(['broker'])
            ->publiclyVisible()
            ->where('is_featured', true)
            ->latest()
            ->limit(6)
            ->get();

        // Fallback: if no featured properties, show latest publicly visible listings
        if ($featuredProperties->isEmpty()) {
            $featuredProperties = Property::with(['broker'])
                ->publiclyVisible()
                ->latest()
                ->limit(6)
                ->get();
        }

        // Get platform statistics
        $stats = [
            'totalProperties' => Property::where('status', 'available')->count(),
            'totalBrokers' => User::where('role', 'broker')->where('is_approved', true)->count(),
            'totalClients' => Client::count(),
            'successRate' => 95 // This could be calculated based on actual transactions
        ];

        // Get top performing brokers using centralized service
        $topBrokers = $this->brokerRankingService->getTopPerformingBrokers(1);

        return Inertia::render('Home', [
            'featuredProperties' => $featuredProperties,
            'stats' => $stats,
            'topBrokers' => $topBrokers
        ]);
    }

    /**
     * Display public property listings with filtering
     */
    public function properties(Request $request)
    {
        $query = Property::with(['broker'])
            ->withCount([
                // Count active (in-progress) transactions to robustly flag under-transaction listings
                'transactions as active_transactions_count' => function ($q) {
                    $q->whereNotIn('status', ['finalized', 'cancelled']);
                },
            ])
            ->select('properties.*') // Ensure all columns from properties table are selected
            // Visibility rules:
            // - Default: show available + in-transaction states (under_negotiation, reserved)
            // - When include_sold=true: show ONLY sold listings
            ->when($request->boolean('include_sold'), function ($q) {
                $q->where('status', 'sold');
            }, function ($q) {
                $q->whereIn('status', ['available', 'under_negotiation', 'reserved']);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('municipality', 'like', "%{$search}%")
                      ->orWhere('barangay', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->types, function ($query, $types) use ($request) {
                $types = is_string($types) ? explode(',', $types) : $types;
                
                \Log::info('PUBLIC - Filtering by types:', [
                    'types_input' => $request->types,
                    'types_parsed' => $types,
                ]);
                
                $query->where(function($q) use ($types) {
                    foreach ($types as $type) {
                        $type = trim($type); // Trim whitespace
                        \Log::info('PUBLIC - Applying type filter: ' . $type);
                        
                        // Handle custom types (prefixed with "custom:")
                        if (str_starts_with($type, 'custom:')) {
                            $customType = substr($type, 7);
                            $q->orWhere('custom_type_text', $customType);
                        } else {
                            // Handle predefined types
                            $q->orWhereJsonContains('types', $type);
                        }
                    }
                });
            })
            ->when(!$request->types && $request->type, function ($query, $type) {
                // Backward compatibility: single type filter
                \Log::info('Filtering by type: ' . $type);
                
                // Check if it's a custom type
                if (str_starts_with($type, 'custom:')) {
                    $customType = substr($type, 7);
                    $query->where('custom_type_text', $customType);
                } else {
                    // Try multiple approaches to find the type
                    $query->where(function($q) use ($type) {
                        // Try JSON contains
                        $q->whereJsonContains('types', $type)
                          // Or try old type column
                          ->orWhere('type', $type)
                          // Or try JSON as string (in case it's stored as text)
                          ->orWhereRaw("JSON_SEARCH(types, 'one', ?) IS NOT NULL", [$type]);
                    });
                }
            })
            ->when($request->municipality, function ($query, $municipality) {
                $query->where('municipality', $municipality);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('total_price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('total_price', '<=', $maxPrice);
            })
            ->when($request->min_area, function ($query, $minArea) {
                $query->where('lot_area_sqm', '>=', $minArea);
            })
            ->when($request->max_area, function ($query, $maxArea) {
                $query->where('lot_area_sqm', '<=', $maxArea);
            })
            ->when($request->boolean('utilities'), function ($query) {
                $query->where('electricity_available', true)
                      ->where('water_source', true);
            })
            ->when($request->boolean('virtual_tour'), function ($query) {
                $query->where('has_virtual_tour', true);
            })
            // NEW: support featured filter from UI
            ->when($request->boolean('featured'), function ($query) {
                $query->where('is_featured', true);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'newest':
                        $query->latest();
                        break;
                    case 'oldest':
                        $query->oldest();
                        break;
                    case 'price_low':
                        $query->orderBy('total_price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('total_price', 'desc');
                        break;
                    case 'area_large':
                        $query->orderBy('lot_area_sqm', 'desc');
                        break;
                    case 'area_small':
                        $query->orderBy('lot_area_sqm', 'asc');
                        break;
                    default:
                        $query->latest();
                }
            }, function ($query) {
                // Default sorting: featured first, then latest
                $query->orderBy('is_featured', 'desc')->latest();
            });

    // Preserve existing query string in pagination links (compatible across Laravel versions)
    $properties = $query->paginate(12)->appends($request->query());

        return Inertia::render('Public/Properties', [
            'properties' => $properties,
            'filters' => $request->only([
                'search', 'type', 'types', 'municipality', 'min_price', 'max_price', 
                'min_area', 'max_area', 'utilities', 'virtual_tour', 'sort', 'featured',
                'include_sold' // new toggle to show sold
            ]),
            'types' => $this->getAllPropertyTypes(),
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    /**
     * Display a single property for public viewing
     */
    public function showProperty($slug)
    {
        // Allow viewing of available, in-transaction (under_negotiation, reserved), and sold properties
        $property = Property::with(['broker', 'client'])
            ->withCount([
                'transactions as active_transactions_count' => function ($q) {
                    $q->whereNotIn('status', ['finalized', 'cancelled']);
                }
            ])
            ->where('slug', $slug)
            ->whereIn('status', ['available', 'under_negotiation', 'reserved', 'sold'])
            ->firstOrFail();
        
        // Ensure broker relationship is loaded with fallback
        if (!$property->relationLoaded('broker') || !$property->broker) {
            $property->load('broker');
        }
        
        // Get similar properties (same type and municipality, excluding current)
        $similarProperties = Property::with(['broker'])
            ->where('status', 'available')
            ->where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('type', $property->type)
                      ->orWhere('municipality', $property->municipality);
            })
            ->limit(4)
            ->get();
    
        return Inertia::render('Public/PropertyDetail', [
            'property' => $property,
            'similarProperties' => $similarProperties
        ]);
    }

    /**
     * Store a public inquiry for a property
     */
    public function storeInquiry(Request $request, Property $property)
    {
        // Only allow inquiries for properties that are truly available
        // Block if status is not 'available' OR if there is any active transaction
        if ($property->status !== 'available' || $property->is_under_transaction) {
            $message = 'This property is currently not accepting new inquiries.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }
            return back()->withErrors(['property' => $message]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        // Create or find client
        $client = Client::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'broker_id' => $property->broker_id
            ]
        );

        // Store inquiry data in session for auto-populating auth forms
        session([
            'inquiry_data' => [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'property_title' => $property->title,
                'property_id' => $property->id,
                'timestamp' => now()->timestamp
            ]
        ]);

        // Create inquiry
        $inquiry = Inquiry::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'assigned_broker_id' => $property->broker_id, // Assign to property owner broker
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'],
            'status' => 'new'
        ]);

        // Load relationships for notification
        $inquiry->load(['property', 'client']);

        // Create conversation for this inquiry to enable messaging
        $conversation = Conversation::createForInquiry($inquiry);

        // Send notification to the property broker
        $property->broker->notify(new NewInquiryNotification($inquiry));
        
        // Broadcast real-time event for immediate dashboard updates
        broadcast(new NewInquiryReceived($inquiry));

        // Return JSON response for API requests, redirect for web requests
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your inquiry has been sent successfully! The broker will contact you soon.',
                'inquiry_id' => $inquiry->id
            ]);
        }

        return back()->with('success', 'Your inquiry has been sent successfully! The broker will contact you soon.');
    }
  
    /**
     * Store inquiry data in session for auth form auto-population
     */
    public function storeInquirySession(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:1000',
            'property_id' => 'nullable|integer|exists:properties,id',
            'property_title' => 'nullable|string|max:255'
        ]);

        // Store inquiry data in session for auto-populating auth forms
        session([
            'inquiry_data' => [
                'name' => $validated['name'] ?? '',
                'email' => $validated['email'] ?? '',
                'phone' => $validated['phone'] ?? '',
                'message' => $validated['message'] ?? '',
                'property_title' => $validated['property_title'] ?? '',
                'property_id' => $validated['property_id'] ?? null,
                'timestamp' => now()->timestamp
            ]
        ]);

        return back()->with('success', 'Inquiry data stored successfully');
    }
}
