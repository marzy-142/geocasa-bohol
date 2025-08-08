<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Notifications\NewInquiryNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Display the home page with featured properties and stats
     */
    public function home()
    {
        // Get featured properties (limit to 6)
        $featuredProperties = Property::with(['broker'])
            ->where('status', 'available')
            ->where('is_featured', true)
            ->latest()
            ->limit(6)
            ->get();

        // Get platform statistics
        $stats = [
            'totalProperties' => Property::where('status', 'available')->count(),
            'totalBrokers' => User::where('role', 'broker')->where('is_approved', true)->count(),
            'totalClients' => Client::count(),
            'successRate' => 95 // This could be calculated based on actual transactions
        ];

        // Get top performing brokers for leaderboard display
        $topBrokers = $this->getTopBrokersForHome();

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
            ->where('status', 'available')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('municipality', 'like', "%{$search}%")
                      ->orWhere('barangay', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
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
            ->when($request->utilities, function ($query) {
                $query->where('electricity_available', true)
                      ->where('water_source', true);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
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

        $properties = $query->paginate(12)->withQueryString();

        return Inertia::render('Public/Properties', [
            'properties' => $properties,
            'filters' => $request->only([
                'search', 'type', 'municipality', 'min_price', 'max_price', 
                'min_area', 'max_area', 'utilities', 'sort'
            ]),
            'types' => Property::TYPES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    /**
     * Display a single property for public viewing
     */
    public function showProperty(Property $property)
    {
        // Only show available properties to public
        if ($property->status !== 'available') {
            abort(404);
        }

        $property->load(['broker', 'client']);

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
        // Only allow inquiries for available properties
        if ($property->status !== 'available') {
            abort(404);
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

        // Create inquiry
        $inquiry = Inquiry::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'message' => $validated['message'],
            'status' => 'pending'
        ]);

        // Load relationships for notification
        $inquiry->load(['property', 'client']);

        // Send notification to the property broker
        $property->broker->notify(new NewInquiryNotification($inquiry));

        return back()->with('success', 'Your inquiry has been sent successfully! The broker will contact you soon.');
    }

    /**
     * Get top brokers for home page display
     */
    private function getTopBrokersForHome($limit = 5)
    {
        return User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'transactions as total_sales' => function ($query) {
                    $query->where('status', 'finalized');
                },
                'properties as active_listings' => function ($query) {
                    $query->where('status', 'available');
                }
            ])
            ->withSum([
                'transactions as total_commission' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ], 'commission_amount')
            ->withSum([
                'transactions as total_sales_value' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ], 'final_price')
            ->get()
            ->map(function ($broker) {
                $broker->total_commission = $broker->total_commission ?? 0;
                $broker->total_sales_value = $broker->total_sales_value ?? 0;
                return $broker;
            })
            ->sortByDesc('total_sales_value')
            ->take($limit)
            ->values();
    }
}