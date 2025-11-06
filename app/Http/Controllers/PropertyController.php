<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Http\Requests\PropertyFileUploadRequest;
use App\Services\DatabaseOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    protected $optimizationService;

    public function __construct(DatabaseOptimizationService $optimizationService)
    {
        $this->optimizationService = $optimizationService;
    }

    /**
     * Get all property types (predefined + custom) for filtering
     */
    private function getAllPropertyTypes()
    {
        // Get predefined types with counts
        $predefinedTypes = collect(Property::TYPES)->map(function($type) {
            $count = Property::whereJsonContains('types', $type)->count();
            return [
                'value' => $type,
                'label' => Property::formatPropertyType($type),
                'count' => $count
            ];
        })->filter(fn($t) => $t['count'] > 0); // Only show if used

        // Get custom types (from properties with "other" type)
        $customTypes = Property::whereJsonContains('types', 'other')
            ->whereNotNull('custom_type_text')
            ->select('custom_type_text')
            ->distinct()
            ->get()
            ->map(function($item) {
                $count = Property::where('custom_type_text', $item->custom_type_text)->count();
                return [
                    'value' => 'custom:' . $item->custom_type_text,
                    'label' => $item->custom_type_text,
                    'count' => $count
                ];
            });

        // Combine and sort
        return $predefinedTypes->concat($customTypes)
            ->sortBy('label')
            ->values()
            ->toArray();
    }

    /**
     * Get property types for a specific broker
     */
    private function getBrokerPropertyTypes($brokerId)
    {
        // Get predefined types with counts for this broker only
        $predefinedTypes = collect(Property::TYPES)->map(function($type) use ($brokerId) {
            $count = Property::where('broker_id', $brokerId)
                ->where(function($q) use ($type) {
                    $q->whereJsonContains('types', $type)
                      ->orWhere('type', $type);
                })
                ->count();
            return [
                'value' => $type,
                'label' => Property::formatPropertyType($type),
                'count' => $count
            ];
        })->filter(fn($t) => $t['count'] > 0); // Only show types this broker has

        // Get custom types for this broker
        $customTypes = Property::where('broker_id', $brokerId)
            ->where(function($q) {
                $q->whereJsonContains('types', 'other')
                  ->orWhere('type', 'other');
            })
            ->whereNotNull('custom_type_text')
            ->select('custom_type_text')
            ->distinct()
            ->get()
            ->map(function($item) use ($brokerId) {
                $count = Property::where('broker_id', $brokerId)
                    ->where('custom_type_text', $item->custom_type_text)
                    ->count();
                return [
                    'value' => 'custom:' . $item->custom_type_text,
                    'label' => $item->custom_type_text,
                    'count' => $count
                ];
            });

        // Combine and sort
        return $predefinedTypes->concat($customTypes)
            ->sortBy('label')
            ->values()
            ->toArray();
    }

    /**
     * Display a listing of properties for admin dashboard.
     */
    public function index(Request $request)
    {
        // Ensure only admins can access this method
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Optimized eager loading with specific columns and counts
        $query = Property::with([
            'broker:id,name,email',
            'client:id,name,email,phone'
        ])->withCount(['inquiries', 'transactions']);
    
        // Apply search and filter conditions using full-text search
        $query = $query->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->types, function ($query, $types) {
                // Support both array and comma-separated string
                if (is_string($types)) {
                    $types = explode(',', $types);
                }
                
                // Normalize non-custom types to slugs
                $normalized = array_map(function($t) {
                    return \App\Models\Property::normalizeTypeInput($t);
                }, $types);

                $query->where(function($q) use ($normalized) {
                    foreach ($normalized as $type) {
                        // Handle custom types (prefixed with "custom:")
                        if (str_starts_with($type, 'custom:')) {
                            $customType = substr($type, 7); // Remove "custom:" prefix
                            $q->orWhere('custom_type_text', $customType);
                        } else {
                            // Handle predefined types
                            $q->orWhereJsonContains('types', $type)
                              ->orWhere('type', $type);
                        }
                    }
                });
            })
            // Backward compatibility: single type filter
            ->when(!$request->types && $request->type, function ($query, $type) {
                $type = \App\Models\Property::normalizeTypeInput($type);
                $query->byType($type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->municipality, function ($query, $municipality) {
                $query->where('municipality', $municipality);
            })
            ->when($request->broker_id, function ($query, $brokerId) {
                $query->where('broker_id', $brokerId);
            })
            ->when($request->min_price || $request->max_price, function ($query) use ($request) {
                 $query->priceRange($request->min_price, $request->max_price);
             })
             ->when($request->min_area || $request->max_area, function ($query) use ($request) {
                 $query->areaRange($request->min_area, $request->max_area);
             })
            ->when($request->utilities, function ($query) {
                $query->where('electricity_available', true)
                      ->where('water_source', true);
            })
            ->when($request->featured, function ($query) {
                $query->where('is_featured', true);
            });
    
    // Preserve query string consistently across Laravel versions
    $properties = $query->latest()->paginate(12)->appends($request->query());

    // Get cached filter options and statistics
    $filterOptions = $this->optimizationService->getPropertyFilterOptions();
    $stats = $this->optimizationService->getPropertyStats();

    $brokers = $filterOptions['brokers'];

    return Inertia::render('Properties/Index', [
        'properties' => $properties,
        'filters' => $request->only(['search', 'type', 'types', 'status', 'municipality', 'broker_id', 'min_price', 'max_price', 'min_area', 'max_area', 'utilities', 'featured']),
        'types' => $this->getAllPropertyTypes(), // Dynamic types including custom
        'statuses' => Property::STATUSES,
        'municipalities' => Property::BOHOL_MUNICIPALITIES,
        'brokers' => $brokers,
        'stats' => $stats,
        'isAdminView' => true
    ]);
    }

    /**
     * Display properties for broker dashboard
     */
    public function brokerIndex(Request $request)
    {
        // Ensure only brokers can access this method
        $this->authorize('viewAny', Property::class);
        
        // Optimized eager loading for broker properties
        $query = Property::with([
            'broker:id,name,email',
            'client:id,name,email,phone'
        ])->withCount(['inquiries', 'transactions'])
        ->where('broker_id', auth()->id());
    
        // Apply search and filter conditions using full-text search
        $query = $query->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->types, function ($query, $types) use ($request) {
                // Support both array and comma-separated string
                if (is_string($types)) {
                    $types = explode(',', $types);
                }
                
                // Debug logging
                \Log::info('BROKER - Filtering by types:', [
                    'broker_id' => auth()->id(),
                    'types_input' => $request->types,
                    'types_parsed' => $types,
                    'is_array' => is_array($types),
                    'count' => is_array($types) ? count($types) : 0
                ]);
                
                // Normalize non-custom types to slugs
                $normalized = array_map(function($t) {
                    return \App\Models\Property::normalizeTypeInput($t);
                }, $types);

                $query->where(function($q) use ($normalized) {
                    foreach ($normalized as $type) {
                        $type = trim($type); // Trim whitespace

                        // Handle custom types (prefixed with "custom:")
                        if (str_starts_with($type, 'custom:')) {
                            $customType = substr($type, 7); // Remove "custom:" prefix
                            $q->orWhere('custom_type_text', $customType);
                            \Log::info('BROKER - Custom type filter:', ['custom_type' => $customType]);
                        } else {
                            // Handle predefined types - check both new 'types' and legacy 'type' fields
                            $q->orWhere(function($subQ) use ($type) {
                                $subQ->whereJsonContains('types', $type)
                                     ->orWhere('type', $type);
                            });
                            \Log::info('BROKER - Standard type filter:', ['type' => $type]);
                        }
                    }
                });
            })
            // Backward compatibility: single type filter
            ->when(!$request->types && $request->type, function ($query, $type) {
                $type = \App\Models\Property::normalizeTypeInput($type);
                $query->byType($type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->municipality, function ($query, $municipality) {
                $query->where('municipality', $municipality);
            })
            ->when($request->min_price || $request->max_price, function ($query) use ($request) {
                 $query->priceRange($request->min_price, $request->max_price);
             })
             ->when($request->min_area || $request->max_area, function ($query) use ($request) {
                 $query->areaRange($request->min_area, $request->max_area);
             })
            ->when($request->utilities, function ($query) {
                $query->where('electricity_available', true)
                      ->where('water_source', true);
            });
    
        $properties = $query->latest()->paginate(12);
    
        // Use the existing Properties/Index component instead of non-existent Broker/Properties/Index
        return Inertia::render('Properties/Index', [
            'properties' => $properties,
            'filters' => $request->only(['search', 'type', 'types', 'status', 'municipality', 'min_price', 'max_price', 'min_area', 'max_area', 'utilities']),
            'types' => $this->getBrokerPropertyTypes(auth()->id()), // Show only types this broker has
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'stats' => [
                'total' => Property::where('broker_id', auth()->id())->count(),
                'active' => Property::where('broker_id', auth()->id())->where('status', 'available')->count(),
                'sold' => Property::where('broker_id', auth()->id())->where('status', 'sold')->count(),
                'pending' => Property::where('broker_id', auth()->id())->where('status', 'pending')->count(),
            ],
            'isBrokerView' => true // Add flag to differentiate broker view from admin view
        ]);
    }

    public function show(Property $property)
    {
        $property->load(['broker', 'client', 'inquiries.client', 'transactions']);
        
        return Inertia::render('Properties/Show', [
            'property' => $property,
        ]);
    }

    /**
     * Display a single property for broker dashboard
     */
    public function brokerShow(Property $property)
    {
        // Ensure only the property owner (broker) can access
        $this->authorize('view', $property);
        
        $property->load(['broker', 'client', 'inquiries.client', 'transactions']);
        
        return Inertia::render('Properties/Show', [
            'property' => $property,
            'isBrokerView' => true
        ]);
    }

    public function create()
    {
        $this->authorize('create', Property::class);
        
        $clients = auth()->user()->role === 'broker'
            ? auth()->user()->clients()->get(['id', 'name', 'email'])
            : collect();

        // Format types as objects with value and label
        $types = collect(Property::TYPES)->map(function($type) {
            return [
                'value' => $type,
                'label' => Property::formatPropertyType($type)
            ];
        })->toArray();

        // Add "Other" option at the end
        $types[] = [
            'value' => 'other',
            'label' => '✏️ Other (specify)'
        ];

        return Inertia::render('Properties/CreateSimple', [
            'clients' => $clients,
            'types' => $types,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'gisConfig' => [
                'enabled' => true,
                'defaultCenter' => [
                    'lng' => 124.1436
                ],
                'zoom' => 10
            ],
            'virtualTourConfig' => [
                'enabled' => true,
                'maxFiles' => 20,
                'allowedTypes' => ['jpg', 'jpeg', 'png']
            ]
        ]);
    }

    public function store(PropertyFileUploadRequest $request)
    {
        $this->authorize('create', Property::class);
    
        $validated = $request->validated();
    
        // Enforce 'available' for broker-created listings
        if (auth()->user()->role === 'broker') {
            $validated['status'] = $validated['status'] ?? 'available';
        }
    
        $validated['broker_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
    
        // Calculate hectares if lot_area_sqm is provided
        if (!empty($validated['lot_area_sqm']) && empty($validated['lot_area_hectares'])) {
            $validated['lot_area_hectares'] = $validated['lot_area_sqm'] / 10000;
        }
    
        // Handle secure file uploads consistently with update method
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties/images', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }
        
        if ($request->hasFile('virtual_tour_images')) {
            $tourImagePaths = [];
            foreach ($request->file('virtual_tour_images') as $image) {
                $path = $image->store('properties/virtual-tours', 'public');
                $tourImagePaths[] = $path;
            }
            $validated['virtual_tour_images'] = $tourImagePaths;
            $validated['has_virtual_tour'] = true;
        } else {
            $validated['has_virtual_tour'] = $validated['has_virtual_tour'] ?? false;
        }
    
        $property = Property::create($validated);

        return redirect()->route('broker.properties.index')
            ->with('success', 'Land property created successfully with enhanced features.');
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);
        
        $clients = auth()->user()->role === 'broker' 
            ? auth()->user()->clients()->get(['id', 'name', 'email'])
            : collect();

        // Build label/value pairs for types and include an 'Other (specify)' option
        $types = collect(Property::TYPES)
            ->map(function ($slug) {
                $label = ucwords(str_replace('_', ' ', $slug));
                return ['value' => $slug, 'label' => $label];
            })
            ->values()
            ->toArray();

        $types[] = ['value' => 'other', 'label' => 'Other (specify)'];

        return Inertia::render('Properties/Edit', [
            'property' => $property,
            'clients' => $clients,
            'types' => $types,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'gisConfig' => [
                'enabled' => true,
                'defaultCenter' => [
                    'lat' => $property->coordinates_lat ?? 9.8349,
                    'lng' => $property->coordinates_lng ?? 124.1436
                ],
                'zoom' => 10
            ],
            'virtualTourConfig' => [
                'enabled' => true,
                'maxFiles' => 20,
                'allowedTypes' => ['jpg', 'jpeg', 'png']
            ]
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);
    
        // Add debugging to track the request data
        \Log::info('PropertyController update method called', [
            'property_id' => $property->id,
            'has_new_virtual_tour_images' => $request->hasFile('new_virtual_tour_images'),
            'new_virtual_tour_images_count' => $request->hasFile('new_virtual_tour_images') ? count($request->file('new_virtual_tour_images')) : 0,
            'current_has_virtual_tour' => $property->has_virtual_tour,
            'request_data_keys' => array_keys($request->all())
        ]);
    
        try {
            \Log::info('About to validate request data');
            // Use unified validation rules from PropertyFileUploadRequest
            $propertyRequest = new \App\Http\Requests\PropertyFileUploadRequest();
            $validated = $request->validate($propertyRequest->rules());
            \Log::info('Validation completed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unexpected error during validation:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }

        // Add debugging after validation
        \Log::info('Validated data:', [
            'has_new_virtual_tour_images_in_validated' => isset($validated['new_virtual_tour_images']),
            'validated_keys' => array_keys($validated)
        ]);
    
        // Update slug if title changed
        if ($validated['title'] !== $property->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        }
    
        // Calculate hectares if lot_area_sqm is provided and hectares is not
        if (!empty($validated['lot_area_sqm']) && empty($validated['lot_area_hectares'])) {
            $validated['lot_area_hectares'] = $validated['lot_area_sqm'] / 10000;
        }
    
        // Handle file removals and additions
        try {
            \Log::info('About to call handleFileUpdates');
            $this->handleFileUpdates($request, $property, $validated);
            \Log::info('handleFileUpdates completed successfully');
        } catch (\Exception $e) {
            \Log::error('Exception in handleFileUpdates:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Add debugging before update
        \Log::info('Before property update:', [
            'validated_has_virtual_tour' => $validated['has_virtual_tour'] ?? 'not_set',
            'validated_virtual_tour_images' => $validated['virtual_tour_images'] ?? 'not_set'
        ]);

        // Preserve status for brokers to avoid breaking transaction-driven visibility.
        // Brokers edit content/media, while lifecycle statuses are managed by transactions/observers.
        if (auth()->user()->role === 'broker') {
            $validated['status'] = $property->status; // keep existing status
        }

        $property->update($validated);

        return redirect()->route('broker.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
    
        // Delete associated files (handle both array and JSON string safely)
        if ($property->images) {
            $images = $property->images;
            if (is_string($images)) {
                $images = json_decode($images, true) ?: [];
            }
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        if ($property->documents) {
            $documents = $property->documents;
            if (is_string($documents)) {
                $documents = json_decode($documents, true) ?: [];
            }
            if (is_array($documents)) {
                foreach ($documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
        }
    
        $property->delete();
        
        // Redirect to the correct listing page based on role
        $redirectRoute = auth()->user()->role === 'admin'
            ? 'admin.properties.index'
            : 'broker.properties.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Land property deleted successfully.');
    }



    private function handleFileUpdates(Request $request, Property $property, array &$validated)
    {
        \Log::info('=== handleFileUpdates START ===');
        
        // Add comprehensive debugging at the start
        \Log::info('handleFileUpdates called:', [
            'has_new_virtual_tour_images_file' => $request->hasFile('new_virtual_tour_images'),
            'new_virtual_tour_images_input' => $request->input('new_virtual_tour_images'),
            'all_files' => array_keys($request->allFiles()),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Handle image removal
        if ($request->has('remove_images')) {
            $currentImages = $property->images ?? [];
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_filter($currentImages, fn($img) => $img !== $imageToRemove);
                }
            }
            $validated['images'] = array_values($currentImages);
        }
    
        // Handle document removal
        if ($request->has('remove_documents')) {
            $currentDocuments = $property->documents ?? [];
            foreach ($request->remove_documents as $documentToRemove) {
                if (in_array($documentToRemove, $currentDocuments)) {
                    Storage::disk('public')->delete($documentToRemove);
                    $currentDocuments = array_filter($currentDocuments, fn($doc) => $doc !== $documentToRemove);
                }
            }
            $validated['documents'] = array_values($currentDocuments);
        }
    
        // Handle new image uploads
        if ($request->hasFile('new_images')) {
            $currentImages = $validated['images'] ?? $property->images ?? [];
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('properties/images', 'public');
                $currentImages[] = $path;
            }
            $validated['images'] = $currentImages;
        }
    
        // Handle new document uploads
        if ($request->hasFile('new_documents')) {
            $currentDocuments = $validated['documents'] ?? $property->documents ?? [];
            foreach ($request->file('new_documents') as $document) {
                $path = $document->store('properties/documents', 'public');
                $currentDocuments[] = $path;
            }
            $validated['documents'] = $currentDocuments;
        }
    
        // Handle panoramic view image removal
        if ($request->has('remove_virtual_tour_images')) {
            $currentTourImages = $property->virtual_tour_images ?? [];
            foreach ($request->remove_virtual_tour_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentTourImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentTourImages = array_filter($currentTourImages, fn($img) => $img !== $imageToRemove);
                }
            }
            $validated['virtual_tour_images'] = array_values($currentTourImages);
        }
    
        // Handle new panoramic view image uploads
        if ($request->hasFile('new_virtual_tour_images')) {
            \Log::info('Processing new panoramic view images');
            $currentTourImages = $validated['virtual_tour_images'] ?? $property->virtual_tour_images ?? [];
            foreach ($request->file('new_virtual_tour_images') as $image) {
                $path = $image->store('properties/virtual-tours', 'public');
                $currentTourImages[] = $path;
            }
            $validated['virtual_tour_images'] = $currentTourImages;
            
            // Add debugging
            \Log::info('Panoramic view images after upload:', [
                'images' => $currentTourImages,
                'count' => count($currentTourImages)
            ]);
        }
        
        // Automatically set has_virtual_tour based on resulting panoramic images
        $finalTourImages = $validated['virtual_tour_images'] ?? $property->virtual_tour_images ?? [];
        $validated['has_virtual_tour'] = !empty($finalTourImages);
        
        // Add debugging
        \Log::info('Setting has_virtual_tour (panoramic):', [
            'final_images' => $finalTourImages,
            'has_virtual_tour' => $validated['has_virtual_tour'],
            'validated_array' => $validated
        ]);
    }


    /**
     * Toggle featured status of a property
     */
    public function toggleFeatured(Property $property)
    {
        $broker = Auth::user();
        
        // Ensure the property belongs to the authenticated broker
        if ($property->broker_id !== $broker->id) {
            abort(403, 'Unauthorized access to property.');
        }

        try {
            // If trying to feature a property, check the limit
            if (!$property->is_featured) {
                $featuredCount = Property::where('broker_id', $broker->id)
                    ->featured()
                    ->count();
                
                $maxFeatured = config('app.max_featured_properties', 5); // Default to 5
                
                if ($featuredCount >= $maxFeatured) {
                    return redirect()->back()->with('error', 
                        "You can only have {$maxFeatured} featured properties at a time. Please un-feature another property first.");
                }
            }
            
            // Toggle the featured status
            $property->is_featured = !$property->is_featured;
            $property->save();
            
            $message = $property->is_featured 
                ? 'Property has been featured successfully!' 
                : 'Property has been removed from featured listings.';
                
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Failed to toggle featured status', [
                'property_id' => $property->id,
                'broker_id' => $broker->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to update featured status. Please try again.');
        }
    }

    /**
     * Auto-replace oldest featured property with new one
     */
    public function autoFeature(Property $property)
    {
        $broker = Auth::user();
        
        // Ensure the property belongs to the authenticated broker
        if ($property->broker_id !== $broker->id) {
            abort(403, 'Unauthorized access to property.');
        }

        try {
            $maxFeatured = config('app.max_featured_properties', 5);
            $featuredProperties = Property::where('broker_id', $broker->id)
                ->featured()
                ->orderBy('updated_at', 'asc') // Oldest first
                ->get();
            
            // If at limit, remove the oldest featured property
            if ($featuredProperties->count() >= $maxFeatured) {
                $oldestFeatured = $featuredProperties->first();
                $oldestFeatured->is_featured = false;
                $oldestFeatured->save();
            }
            
            // Feature the new property
            $property->is_featured = true;
            $property->save();
            
            $message = $featuredProperties->count() >= $maxFeatured
                ? 'Property featured successfully! The oldest featured property has been automatically removed.'
                : 'Property has been featured successfully!';
                
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Failed to auto-feature property', [
                'property_id' => $property->id,
                'broker_id' => $broker->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to feature property. Please try again.');
        }
    }
}