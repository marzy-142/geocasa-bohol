<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientViewedProperty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    /**
     * Get all property types (predefined + custom) for filtering
     */
    private function getAllPropertyTypes()
    {
        // Get predefined types with counts (only available properties for clients)
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
     * Display property search and listing for clients
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get or create client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        if (!$client) {
            $client = Client::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        } elseif (!$client->user_id) {
            $client->update(['user_id' => $user->id]);
        }

        // Build property query - same as public properties
        $query = Property::with(['broker:id,name,email,phone'])
            ->select('properties.*') // Ensure all columns including images are selected
            ->where('status', 'available');

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('municipality', 'like', "%{$search}%")
                  ->orWhere('barangay', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('types')) {
            $types = is_string($request->types) ? explode(',', $request->types) : $request->types;
            
            $query->where(function($q) use ($types) {
                foreach ($types as $type) {
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
        } elseif ($request->filled('type')) {
            // Backward compatibility: single type filter
            $type = $request->type;
            if (str_starts_with($type, 'custom:')) {
                $customType = substr($type, 7);
                $query->where('custom_type_text', $customType);
            } else {
                $query->where(function($q) use ($type) {
                    $q->whereJsonContains('types', $type)
                      ->orWhere('type', $type)
                      ->orWhereRaw("JSON_SEARCH(types, 'one', ?) IS NOT NULL", [$type]);
                });
            }
        }

        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }

        if ($request->filled('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }

        if ($request->filled('min_area')) {
            $query->where('lot_area_sqm', '>=', $request->min_area);
        }

        if ($request->filled('max_area')) {
            $query->where('lot_area_sqm', '<=', $request->max_area);
        }

        if ($request->boolean('utilities')) {
            $query->where('electricity_available', true)
                  ->where('water_source', true);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->boolean('virtual_tour')) {
            $query->where('has_virtual_tour', true);
        }

        // Apply sorting - include relevance
        $query->when($request->sort, function ($query, $sort) use ($request) {
            switch ($sort) {
                case 'relevance':
                    // If a search term is present, rank by field matches
                    if ($request->filled('search')) {
                        $term = $request->get('search');
                        // Weighted relevance: title (3), municipality/address (2), description (1)
                        $query->orderByRaw(
                            "((CASE WHEN title LIKE ? THEN 3 ELSE 0 END)
                             + (CASE WHEN municipality LIKE ? THEN 2 ELSE 0 END)
                             + (CASE WHEN address LIKE ? THEN 2 ELSE 0 END)
                             + (CASE WHEN description LIKE ? THEN 1 ELSE 0 END)) DESC",
                            ["%{$term}%", "%{$term}%", "%{$term}%", "%{$term}%"]
                        );
                        // Secondary sort: featured first, then newest
                        $query->orderBy('is_featured', 'desc')->latest();
                    } else {
                        // No search term: fall back to featured + newest
                        $query->orderBy('is_featured', 'desc')->latest();
                    }
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
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->latest();
            }
        }, function ($query) {
            // Default sorting: featured first, then latest
            $query->orderBy('is_featured', 'desc')->latest();
        });

        $properties = $query->paginate(12);

        // Get saved properties for this client using the proper relationship
        $savedProperties = $client->savedProperties()->get();

        return Inertia::render('Client/Properties', [
            'properties' => $properties,
            'savedProperties' => $savedProperties,
            'types' => $this->getAllPropertyTypes(),
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'filters' => $request->only([
                'search', 'type', 'types', 'municipality', 'min_price', 'max_price',
                'min_area', 'max_area', 'utilities', 'featured', 'virtual_tour', 'sort'
            ]),
        ]);
    }

    /**
     * Show property details for authenticated client
     */
    public function show($property)
    {
        $user = auth()->user();
        
        // Find property or fail with 404
        $property = Property::findOrFail($property);
        
        // Get or create client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        if (!$client) {
            $client = Client::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        }

        // Load property with relationships
        $property->load(['broker:id,name,email,phone,office_address,office_contact_number']);

        // Track property view
        ClientViewedProperty::firstOrCreate([
            'client_id' => $client->id,
            'property_id' => $property->id,
        ], [
            'viewed_at' => now(),
        ]);

        // Check if property is saved
        $isSaved = $client->savedProperties()->where('property_id', $property->id)->exists();

        // Check if client has already inquired about this property
        $hasInquired = $client->inquiries()->where('property_id', $property->id)->exists();

        // Get client's inquiry if exists
        $clientInquiry = $client->inquiries()->where('property_id', $property->id)->latest()->first();

        // Get similar properties
        $similarProperties = Property::where('status', 'available')
            ->where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('type', $property->type)
                      ->orWhere('municipality', $property->municipality);
            })
            ->with(['broker:id,name'])
            ->limit(4)
            ->get();

        return Inertia::render('Client/PropertyDetail', [
            'property' => $property,
            'isSaved' => $isSaved,
            'hasInquired' => $hasInquired,
            'clientInquiry' => $clientInquiry,
            'similarProperties' => $similarProperties,
            'client' => $client,
        ]);
    }

    /**
     * Save a property to client's favorites
     */
    public function save(Request $request, Property $property)
    {
        \Log::info('Save property method called', ['property_id' => $property->id, 'user_id' => auth()->id()]);
        
        $user = auth()->user();
        
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client) {
            \Log::error('Client not found for user', ['user_id' => $user->id]);
            return back()->with('error', 'Client not found');
        }

        // Use proper pivot table for saved properties
        $isSaved = $client->savedProperties()->where('property_id', $property->id)->exists();
        
        if (!$isSaved) {
            $client->savedProperties()->attach($property->id);
            \Log::info('Property saved successfully', ['property_id' => $property->id, 'client_id' => $client->id]);
        } else {
            \Log::info('Property already saved', ['property_id' => $property->id, 'client_id' => $client->id]);
        }

        return back()->with('success', 'Property saved to favorites');
    }

    /**
     * Remove a property from client's favorites
     */
    public function unsave(Request $request, Property $property)
    {
        $user = auth()->user();
        
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client) {
            return back()->with('error', 'Client not found');
        }

        // Use proper pivot table for saved properties
        $client->savedProperties()->detach($property->id);

        return back()->with('success', 'Property removed from favorites');
    }

    /**
     * Compare selected properties
     */
    public function compare(Request $request)
    {
        $propertyIds = explode(',', $request->get('ids', ''));
        
        if (count($propertyIds) < 2) {
            return redirect()->back()->with('error', 'Please select at least 2 properties to compare');
        }

        $properties = Property::with(['broker'])
            ->whereIn('id', $propertyIds)
            ->get();

        return Inertia::render('Client/PropertyCompare', [
            'properties' => $properties,
        ]);
    }

    /**
     * Display client's saved properties
     */
    public function saved(Request $request)
    {
        $user = auth()->user();
        
        // Get or create client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        if (!$client) {
            $client = Client::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        } elseif (!$client->user_id) {
            $client->update(['user_id' => $user->id]);
        }

        // Get saved properties using proper relationship
        $savedProperties = $client->savedProperties()
            ->with(['broker:id,name,email,phone'])
            ->get();
        
        // Format as paginated response for consistency with main properties page
        $formattedProperties = [
            'data' => $savedProperties,
            'total' => $savedProperties->count(),
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $savedProperties->count(),
        ];
        
        return Inertia::render('Client/Properties', [
            'properties' => $formattedProperties,
            'savedProperties' => $savedProperties,
            'types' => Property::TYPES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'filters' => $request->only([
                'search', 'type', 'municipality', 'min_price', 'max_price',
                'min_area', 'max_area', 'utilities', 'featured', 'virtual_tour', 'sort'
            ]),
            'isSavedView' => true,
        ]);
    }
}



