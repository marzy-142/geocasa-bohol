<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Http\Requests\PropertyFileUploadRequest;
use App\Services\DatabaseOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
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
    
        $properties = $query->latest()->paginate(12)->withQueryString();
        
        // Get cached filter options and statistics
        $filterOptions = $this->optimizationService->getPropertyFilterOptions();
        $stats = $this->optimizationService->getPropertyStats();
        
        $brokers = $filterOptions['brokers'];
    
        return Inertia::render('Properties/Index', [
            'properties' => $properties,
            'filters' => $request->only(['search', 'type', 'status', 'municipality', 'broker_id', 'min_price', 'max_price', 'min_area', 'max_area', 'utilities', 'featured']),
            'types' => Property::TYPES,
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
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
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
            'filters' => $request->only(['search', 'type', 'status', 'municipality', 'min_price', 'max_price', 'min_area', 'max_area', 'utilities']),
            'types' => Property::TYPES,
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
    
        return Inertia::render('Properties/Create', [
            'clients' => $clients,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
            'gisConfig' => [
                'enabled' => true,
                'defaultCenter' => [
                    'lat' => 9.8349,
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
            $validated['status'] = 'available';
        }
    
        $validated['broker_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
    
        // Calculate hectares if not provided
        if (empty($validated['lot_area_hectares'])) {
            $validated['lot_area_hectares'] = $validated['lot_area_sqm'] / 10000;
        }
    
        // Map frontend field names to database column names
        $validated['latitude'] = $validated['coordinates_lat'] ?? null;
        $validated['longitude'] = $validated['coordinates_lng'] ?? null;
        $validated['price'] = $validated['total_price'];
        $validated['property_type'] = $validated['type'];
        $validated['city'] = $validated['municipality'];
        $validated['province'] = $validated['barangay'];
    
        // Handle secure file uploads
        $storedFiles = $request->storeFilesSecurely([
            'images',
            'virtual_tour_images'
        ], 'properties', 'public');
        
        if (isset($storedFiles['images'])) {
            $validated['images'] = $storedFiles['images'];
        }
        
        if (isset($storedFiles['virtual_tour_images'])) {
            $validated['virtual_tour_images'] = $storedFiles['virtual_tour_images'];
            $validated['has_virtual_tour'] = true;
        } else {
            $validated['has_virtual_tour'] = false;
        }
    
        $property = Property::create($validated);
        
        // Set expiry date for new properties (90 days from creation)
        $property->setExpiryDate(90);

        return redirect()->route('broker.properties.index')
            ->with('success', 'Land property created successfully with enhanced features.');
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);
        
        $clients = auth()->user()->role === 'broker' 
            ? auth()->user()->clients()->get(['id', 'name', 'email'])
            : collect();

        return Inertia::render('Properties/Edit', [
            'property' => $property,
            'clients' => $clients,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
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
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:' . implode(',', Property::TYPES),
                'status' => 'required|in:' . implode(',', Property::STATUSES),
                'price_per_sqm' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'address' => 'required|string|max:500',
                'municipality' => 'required|in:' . implode(',', Property::BOHOL_MUNICIPALITIES),
                'barangay' => 'required|string|max:100',
                'lot_area_sqm' => 'required|numeric|min:1',
                'lot_area_hectares' => 'nullable|numeric|min:0',
                'title_type' => 'nullable|in:titled,tax_declared,mother_title,cct',
                'title_number' => 'nullable|string|max:100',
                'tax_declaration_number' => 'nullable|string|max:100',
                'coordinates_lat' => 'nullable|numeric|between:-90,90',
                'coordinates_lng' => 'nullable|numeric|between:-180,180',
                'road_access' => 'boolean',
                'water_source' => 'boolean',
                'electricity_available' => 'boolean',
                'internet_available' => 'boolean',
                'nearby_landmarks' => 'nullable|array',
                'zoning_classification' => 'nullable|string|max:100',
                'client_id' => 'nullable|exists:clients,id',
                'new_images' => 'nullable|array|max:10',
                'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'new_documents' => 'nullable|array|max:5',
                'new_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'string',
                'remove_documents' => 'nullable|array',
                'remove_documents.*' => 'string',
                // Add missing virtual tour validation rules for update
                'new_virtual_tour_images' => 'nullable|array|max:20',
                // Changed from 'image|mimes:jpeg,png,jpg|max:5120' to 'file|mimes:jpeg,png,jpg|max:5120'
                'new_virtual_tour_images.*' => 'file|mimes:jpeg,png,jpg|max:5120',
                'remove_virtual_tour_images' => 'nullable|array',
                'remove_virtual_tour_images.*' => 'string',
                'has_virtual_tour' => 'boolean',
                'gis_data' => 'nullable|json',
                'tour_hotspots' => 'nullable|json',
            ]);
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
    
        // Calculate hectares if not provided (safe on missing key)
        if (empty($validated['lot_area_hectares'])) {
            $validated['lot_area_hectares'] = !empty($validated['lot_area_sqm'])
                ? $validated['lot_area_sqm'] / 10000
                : null;
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

        // Keep brokers from hiding properties from public inadvertently
        if (auth()->user()->role === 'broker') {
            $validated['status'] = 'available';
        }

        $property->update($validated);
        
        // Reset expiry date when property is updated (90 days from update)
        $property->setExpiryDate(90);

        return redirect()->route('broker.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
    
        // Delete associated files
        if ($property->images) {
            $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        }
    
        if ($property->documents) {
            $documents = is_array($property->documents) ? $property->documents : json_decode($property->documents, true);
            if (is_array($documents)) {
                foreach ($documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
        }
    
        $property->delete();
    
        return redirect()->route('properties.index')
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
    
        // Handle virtual tour image removal
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
    
        // Handle new virtual tour image uploads
        if ($request->hasFile('new_virtual_tour_images')) {
            \Log::info('Processing new virtual tour images');
            $currentTourImages = $validated['virtual_tour_images'] ?? $property->virtual_tour_images ?? [];
            foreach ($request->file('new_virtual_tour_images') as $image) {
                $path = $image->store('properties/virtual-tours', 'public');
                $currentTourImages[] = $path;
            }
            $validated['virtual_tour_images'] = $currentTourImages;
            
            // Add debugging
            \Log::info('Virtual tour images after upload:', [
                'images' => $currentTourImages,
                'count' => count($currentTourImages)
            ]);
        }
        
        // Automatically set has_virtual_tour based on resulting virtual tour images
        $finalTourImages = $validated['virtual_tour_images'] ?? $property->virtual_tour_images ?? [];
        $validated['has_virtual_tour'] = !empty($finalTourImages);
        
        // Add debugging
        \Log::info('Setting has_virtual_tour:', [
            'final_images' => $finalTourImages,
            'has_virtual_tour' => $validated['has_virtual_tour'],
            'validated_array' => $validated
        ]);
    }

    /**
     * Show property renewals dashboard for broker
     */
    public function renewals()
    {
        $broker = Auth::user();
        
        // Get properties expiring in the next 30 days
        $expiringProperties = Property::where('broker_id', $broker->id)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'pending_renewal')
            ->with(['inquiries' => function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function($property) {
                $property->expires_at_human = $property->expiry_date->diffForHumans();
                $property->days_until_expiry = now()->diffInDays($property->expiry_date, false);
                return $property;
            });
        
        // Get expired properties
        $expiredProperties = Property::where('broker_id', $broker->id)
            ->where('expiry_date', '<', now())
            ->where('status', '!=', 'pending_renewal')
            ->with(['inquiries' => function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderBy('expiry_date', 'desc')
            ->get()
            ->map(function($property) {
                $property->expires_at_human = $property->expiry_date->diffForHumans();
                $property->days_until_expiry = now()->diffInDays($property->expiry_date, false);
                return $property;
            });
        
        // Get recently renewed properties (last 30 days)
        $renewedProperties = Property::where('broker_id', $broker->id)
            ->where('renewed_at', '>=', now()->subDays(30))
            ->with(['inquiries' => function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderBy('renewed_at', 'desc')
            ->get()
            ->map(function($property) {
                $property->expires_at_human = $property->expiry_date->diffForHumans();
                $property->renewed_at_human = $property->renewed_at->diffForHumans();
                return $property;
            });
        
        // Calculate stats
        $stats = [
            'expiring_count' => $expiringProperties->count(),
            'expired_count' => $expiredProperties->count(),
            'renewed_count' => $renewedProperties->count(),
            'total_properties' => Property::where('broker_id', $broker->id)->count()
        ];
        
        return Inertia::render('Broker/PropertyRenewals', [
            'expiring_properties' => $expiringProperties,
            'expired_properties' => $expiredProperties,
            'renewed_properties' => $renewedProperties,
            'stats' => $stats
        ]);
    }

    /**
     * Renew a property listing
     */
    public function renew(Property $property)
    {
        $broker = Auth::user();
        
        // Ensure the property belongs to the authenticated broker
        if ($property->broker_id !== $broker->id) {
            abort(403, 'Unauthorized access to property.');
        }
        
        try {
            // Set new expiry date (90 days from now)
            $property->setExpiryDate(90);
            
            // Update status to active if it was expired or pending renewal
            if (in_array($property->status, ['expired', 'pending_renewal'])) {
                $property->status = 'active';
            }
            
            // Set renewed timestamp
            $property->renewed_at = now();
            $property->save();
            
            \Log::info('Property renewed successfully', [
                'property_id' => $property->id,
                'broker_id' => $broker->id,
                'new_expiry_date' => $property->expiry_date,
                'renewed_at' => $property->renewed_at
            ]);
            
            return redirect()->back()->with('success', 'Property renewed successfully! Your listing is now active for another 90 days.');
            
        } catch (\Exception $e) {
            \Log::error('Property renewal failed', [
                'property_id' => $property->id,
                'broker_id' => $broker->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to renew property. Please try again.');
        }
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