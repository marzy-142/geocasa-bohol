<?php

namespace App\Http\Controllers;

use App\Models\SellerRequest;
use App\Models\Property;
use App\Models\User;
use App\Http\Requests\SellerRequestUploadRequest;
use App\Http\Requests\SimpleSellerRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Notifications\SellerRequestNotification;
use App\Notifications\BrokerSellerAssignmentNotification;
use App\Mail\SellerBrokerAssignedMail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class SellerRequestController extends Controller
{
    /**
     * Display a listing of seller requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on user role
        $query = SellerRequest::with(['assignedBroker', 'reviewedBy', 'property']);
        
        // Role-based filtering
        if ($user->role === 'broker') {
            $query->where('assigned_broker_id', $user->id);
        }
        
        // Enhanced filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Assignment status filter (admin only)
        if ($user->role === 'admin' && $request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('assigned_broker_id');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('assigned_broker_id');
            }
        }

        // Broker filter (admin only)
        if ($user->role === 'admin' && $request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('property_title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        


        if ($request->filled('price_min')) {
            $query->where('asking_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('asking_price', '<=', $request->price_max);
        }
        
        $sellerRequests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get filter options
        $brokers = User::where('role', 'broker')->where('is_approved', true)->get(['id', 'name']);
        
        // Calculate statistics for admin users
        $stats = null;
        if ($user->role === 'admin') {
            $totalSellerRequests = SellerRequest::count();
            $assignedSellerRequests = SellerRequest::whereNotNull('assigned_broker_id')->count();
            $unassignedSellerRequests = SellerRequest::whereNull('assigned_broker_id')->count();
            $activeBrokers = User::where('role', 'broker')
                ->where('is_approved', true)
                ->whereHas('assignedSellerRequests')
                ->count();
            
            $stats = [
                'unassigned' => $unassignedSellerRequests,
                'assigned' => $assignedSellerRequests,
                'activeBrokers' => $activeBrokers,
                'avgSellerRequestsPerBroker' => $activeBrokers > 0 ? round($assignedSellerRequests / $activeBrokers, 1) : 0,
            ];
        }
        
        return Inertia::render('SellerRequests/Index', [
            'sellerRequests' => $sellerRequests,
            'brokers' => $brokers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'assignment_status', 'broker_id', 'date_from', 'date_to', 'price_min', 'price_max']),
            'canManage' => in_array($user->role, ['admin', 'broker']),
            'canCreate' => true
        ]);
    }

    /**
     * Show the form for creating a new seller request (public form)
     */
    public function create()
    {
        // Get available features for the form
        $availableFeatures = [
            'Swimming Pool', 'Garden', 'Parking', 'Security', 'Furnished',
            'Air Conditioning', 'Balcony', 'Terrace', 'Fireplace', 'Storage',
            'Laundry Room', 'Gym', 'Playground', 'Near Beach', 'Mountain View',
            'City View', 'Gated Community', 'Pet Friendly', 'Solar Panels'
        ];

        // Get available verified brokers for selection
        $availableBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->where('prc_verified', true)
            ->whereNull('suspended_at')
            ->select([
                'id',
                'name',
                'brokerage_firm_name',
                'city',
                'years_experience',
                'office_contact_number'
            ])
            ->withCount(['properties as active_listings' => function($q) {
                $q->where('status', 'available');
            }])
            ->withCount(['assignedSellerRequests as pending_requests' => function($q) {
                $q->whereIn('status', ['pending', 'under_review', 'approved']);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($broker) {
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'firm' => $broker->brokerage_firm_name,
                    'location' => $broker->city,
                    'experience' => $broker->years_experience,
                    'active_listings' => $broker->active_listings,
                    'workload' => $broker->pending_requests,
                    'availability' => $broker->pending_requests < 5 ? 'Available' : 'Busy'
                ];
            });

        return Inertia::render('SellerRequests/Create', [
            'availableFeatures' => $availableFeatures,
            'availableBrokers' => $availableBrokers,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    /**
     * Store a newly created seller request - SIMPLIFIED VERSION
     */
    public function store(SimpleSellerRequestRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Handle file uploads - SIMPLIFIED
            $storedFiles = $this->handleFileUploads($request);
            
            // Determine assignment method and broker
            $assignmentMethod = $validated['broker_selection_method'] ?? 'auto';
            $preferredBrokerId = $validated['preferred_broker_id'] ?? null;
            $assignedBrokerId = null;
            $status = 'pending';

            // If seller chose a broker manually
            if ($assignmentMethod === 'manual' && $preferredBrokerId) {
                $assignedBrokerId = $preferredBrokerId;
                $status = 'assigned';
            }

            // Create seller request (aligned to simplified public form)
            $sellerRequest = SellerRequest::create([
                'name' => $validated['contact_name'],
                'email' => $validated['contact_email'],
                'phone' => $validated['contact_phone'],
                'address' => $validated['address'] ?? null,
                'property_title' => $validated['property_title'],
                'property_description' => $validated['property_description'],
                'property_type' => is_array($validated['property_type']) ? json_encode($validated['property_type']) : $validated['property_type'],
                'asking_price' => $validated['asking_price'],
                // Map municipality and barangay from form
                'municipality' => $validated['municipality'] ?? null,
                'barangay' => $validated['barangay'] ?? null,
                'city' => $validated['municipality'] ?? null,
                'province' => 'Bohol',
                'postal_code' => $validated['postal_code'] ?? null,
                'lot_area' => $validated['lot_area_sqm'] ?? null,
                'features' => isset($validated['features']) ? (is_array($validated['features']) ? json_encode($validated['features'], JSON_UNESCAPED_SLASHES) : $validated['features']) : null,
                'uploaded_images' => isset($storedFiles['property_images']) ? json_encode($storedFiles['property_images'], JSON_UNESCAPED_SLASHES) : (isset($storedFiles['uploaded_images']) ? json_encode($storedFiles['uploaded_images'], JSON_UNESCAPED_SLASHES) : null),
                'property_documents' => isset($storedFiles['property_documents']) ? json_encode($storedFiles['property_documents'], JSON_UNESCAPED_SLASHES) : null,
                'ownership_documents' => isset($storedFiles['ownership_documents']) ? json_encode($storedFiles['ownership_documents'], JSON_UNESCAPED_SLASHES) : null,
                'title_type' => $validated['title_type'] ?? null,
                'title_number' => $validated['title_number'] ?? null,
                'zoning_classification' => $validated['zoning_classification'] ?? null,
                'road_access' => $validated['road_access'] ?? false,
                'water_source' => $validated['water_source'] ?? false,
                'electricity_available' => $validated['electricity'] ?? false,
                'internet_available' => $validated['internet'] ?? false,
                'coordinates_lat' => $validated['coordinates_lat'] ?? null,
                'coordinates_lng' => $validated['coordinates_lng'] ?? null,
                'nearby_landmarks' => $validated['nearby_landmarks'] ?? null,
                'price_expectation' => $validated['price_expectation'] ?? null,
                'custom_property_type' => $validated['custom_property_type'] ?? null,
                'availability' => $validated['availability'] ?? null,
                // Urgency not collected in simplified form; default to 'medium'
                'urgency' => $validated['urgency'] ?? 'medium',
                'additional_notes' => $validated['additional_notes'] ?? null,
                'marketing_consent' => $validated['marketing_consent'] ?? false,
                'terms_accepted' => $validated['terms_accepted'],
                'status' => $status,
                'assigned_broker_id' => $assignedBrokerId,
                'assignment_method' => $assignmentMethod,
                'assigned_at' => $assignedBrokerId ? now() : null,
                'wants_broker_selection' => $assignmentMethod === 'manual',
            ]);

            // If auto-assignment, assign broker using smart algorithm
            if ($assignmentMethod === 'auto') {
                $broker = $this->autoAssignBroker($sellerRequest);
                
                if ($broker) {
                    $sellerRequest->update([
                        'assigned_broker_id' => $broker->id,
                        'status' => 'assigned',
                        'assigned_at' => now()
                    ]);
                    $assignedBrokerId = $broker->id;
                }
            }

            // Notify assigned broker
            if ($assignedBrokerId) {
                $broker = User::find($assignedBrokerId);
                if ($broker) {
                    // Get the assigner (authenticated user or null for public submissions)
                    $assignedBy = Auth::check() ? Auth::user() : null;
                    
                    // Send notification (works with or without authenticated user)
                    $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, $assignedBy, 'assigned'));
                }
            }

            // Send confirmation email to seller
            try {
                $assignedBroker = $assignedBrokerId ? User::find($assignedBrokerId) : null;
                Mail::to($sellerRequest->email)->send(
                    new \App\Mail\SellerRequestConfirmationMail($sellerRequest, $assignedBroker, $assignmentMethod)
                );
                
                Log::info('Seller confirmation email sent', [
                    'seller_request_id' => $sellerRequest->id,
                    'seller_email' => $sellerRequest->email
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                Log::error('Failed to send seller confirmation email', [
                    'seller_request_id' => $sellerRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            DB::commit();
            
            // Get broker details if assigned
            $brokerData = null;
            if ($assignedBrokerId) {
                $assignedBroker = User::find($assignedBrokerId);
                if ($assignedBroker) {
                    $brokerData = [
                        'name' => $assignedBroker->name,
                        'brokerage_firm_name' => $assignedBroker->brokerage_firm_name,
                        'office_contact_number' => $assignedBroker->office_contact_number,
                        'city' => $assignedBroker->city,
                        'province' => $assignedBroker->province,
                        'years_experience' => $assignedBroker->years_experience,
                    ];
                }
            }
            
            return Inertia::render('SellerRequests/Success', [
                'sellerRequest' => $sellerRequest,
                'assignedBroker' => $brokerData,
                'assignmentMethod' => $assignmentMethod,
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Seller request submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['uploaded_images', 'property_documents', 'ownership_documents'])
            ]);
            
            return back()->withInput()->withErrors([
                'submission' => 'An error occurred while submitting your request. Please try again.'
            ]);
        }
    }

    /**
     * Handle file uploads with simplified logic
     */
    private function handleFileUploads($request): array
    {
        $storedFiles = [];
        
        // Handle property images (check both field names for compatibility)
        if ($request->hasFile('property_images')) {
            $images = $request->file('property_images');
            foreach ($images as $image) {
                $filename = time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('seller-requests/images', $filename, 'public');
                $storedFiles['property_images'][] = $path;
            }
        } elseif ($request->hasFile('uploaded_images')) {
            $images = $request->file('uploaded_images');
            foreach ($images as $image) {
                $filename = time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('seller-requests/images', $filename, 'public');
                $storedFiles['uploaded_images'][] = $path;
            }
        }
        
        // Handle property documents
        if ($request->hasFile('property_documents')) {
            $docs = $request->file('property_documents');
            foreach ($docs as $doc) {
                $filename = time() . '_' . Str::random(8) . '.' . $doc->getClientOriginalExtension();
                $path = $doc->storeAs('seller-requests/documents', $filename, 'public');
                $storedFiles['property_documents'][] = $path;
            }
        }
        
        // Handle ownership documents
        if ($request->hasFile('ownership_documents')) {
            $docs = $request->file('ownership_documents');
            foreach ($docs as $doc) {
                $filename = time() . '_' . Str::random(8) . '.' . $doc->getClientOriginalExtension();
                $path = $doc->storeAs('seller-requests/ownership', $filename, 'public');
                $storedFiles['ownership_documents'][] = $path;
            }
        }
        
        return $storedFiles;
    }

    /**
     * Send notifications to admins and brokers with enhanced error handling
     */
    private function sendNotifications(SellerRequest $sellerRequest): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            $admins = User::where('role', 'admin')
                ->where('is_active', true)
                ->get();
            $brokers = User::where('role', 'broker')
                ->where('is_approved', true)
                ->where('is_active', true)
                ->get();
            $recipients = $admins->merge($brokers);
            if ($recipients->isEmpty()) {
                Log::warning('No active recipients found for seller request notifications', [
                    'seller_request_id' => $sellerRequest->id
                ]);
                return $results;
            }
            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new SellerRequestNotification($sellerRequest));
                    $results['sent']++;
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error('Failed to send notification to recipient', [
                        'seller_request_id' => $sellerRequest->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            return $results;
        } catch (Exception $e) {
            Log::error('Failed to send notifications: ' . $e->getMessage());
            $results['failed']++;
            $results['errors'][] = [
                'error' => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * Display the specified seller request
     */
    public function show(SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'broker' && 
            $sellerRequest->assigned_broker_id !== $user->id && 
            $sellerRequest->assigned_broker_id !== null) {
            abort(403, 'You can only view requests assigned to you.');
        }

        $sellerRequest->load(['assignedBroker', 'reviewedBy', 'property']);

        return Inertia::render('SellerRequests/Show', [
            'sellerRequest' => $sellerRequest,
            'canManage' => in_array($user->role, ['admin', 'broker']),
            'canAssign' => $user->role === 'admin',
            'brokers' => User::where('role', 'broker')->where('is_approved', true)->get(['id', 'name'])
        ]);
    }

    /**
     * Show the form for editing the specified seller request
     */
    public function edit(SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        // Only allow editing if user is admin or assigned broker
        if ($user->role === 'broker' && $sellerRequest->assigned_broker_id !== $user->id) {
            abort(403, 'You can only edit requests assigned to you.');
        }

        return Inertia::render('SellerRequests/Edit', [
            'sellerRequest' => $sellerRequest,
            'brokers' => User::where('role', 'broker')->where('is_approved', true)->get(['id', 'name'])
        ]);
    }

    /**
     * Update the specified seller request
     */
    public function update(Request $request, SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'broker' && $sellerRequest->assigned_broker_id !== $user->id) {
            abort(403, 'You can only update requests assigned to you.');
        }

        $validated = $request->validate([
            'seller_name' => 'required|string|max:255',
            'seller_email' => 'required|email|max:255',
            'seller_phone' => 'nullable|string|max:20',
            'seller_address' => 'nullable|string',
            'property_title' => 'required|string|max:255',
            'property_description' => 'required|string',
            'asking_price' => 'required|numeric|min:0',
            'property_area' => 'required|numeric|min:0',
            'area_unit' => 'required|in:sqm,acres,hectares',
            'property_location' => 'required|string|max:255',
            'property_address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'features' => 'nullable|array',
                'status' => 'required|in:pending,under_review,assigned,approved,rejected,listed',
            'admin_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
            'assigned_broker_id' => 'nullable|exists:users,id'
        ]);

        // Only admins can change status and assignment
        if ($user->role !== 'admin') {
            unset($validated['status'], $validated['assigned_broker_id']);
        }

    // Only update columns that actually exist in the DB schema to avoid errors
    $sellerRequest->update($this->filterExistingColumns($validated));

        return redirect()->route('seller-requests.show', $sellerRequest)
            ->with('message', 'Seller request updated successfully.');
    }

    /**
     * Remove the specified seller request
     */
    public function destroy(SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        // Only admins can delete requests
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can delete seller requests.');
        }

        // Delete associated images
        if ($sellerRequest->uploaded_images) {
            foreach ($sellerRequest->uploaded_images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $sellerRequest->delete();

        return redirect()->route('seller-requests.index')
            ->with('message', 'Seller request deleted successfully.');
    }

    /**
     * Update request status
     */
    public function updateStatus(Request $request, SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'broker'])) {
            abort(403, 'Unauthorized to update status.');
        }

        $validated = $request->validate([
            // Include 'assigned' so brokers can accept/decline when pre-selected
            'status' => 'required|in:pending,under_review,assigned,approved,rejected,listed',
            'admin_notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:500|required_if:status,rejected',
            'assigned_broker_id' => 'nullable|exists:users,id'
        ]);

        // Validate broker assignment
        if (!empty($validated['assigned_broker_id'])) {
            $broker = User::find($validated['assigned_broker_id']);
            if (!$broker || $broker->role !== 'broker' || !$broker->is_approved) {
                return back()->withErrors(['assigned_broker_id' => 'Invalid broker selection.']);
            }
        }

        try {
            DB::beginTransaction();

            $oldBrokerId = $sellerRequest->assigned_broker_id;
            // If no broker explicitly provided and none assigned yet, auto-attach current broker user
            $newBrokerId = $validated['assigned_broker_id']
                ?? $sellerRequest->assigned_broker_id
                ?? ($user->role === 'broker' ? $user->id : null);

            // Prepare update payload but only include columns that exist
            $updateData = [
                'status' => $validated['status'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
                'assigned_broker_id' => $newBrokerId,
                'reviewed_by' => $user->id,
                'reviewed_at' => now()
            ];

            if (isset($validated['admin_notes']) && Schema::hasColumn('seller_requests', 'admin_notes')) {
                $updateData['admin_notes'] = $validated['admin_notes'];
            }

            $sellerRequest->update($this->filterExistingColumns($updateData));

            // Send notifications if broker assignment changed
            if ($newBrokerId && $oldBrokerId !== $newBrokerId) {
                $broker = User::find($newBrokerId);
                if ($broker) {
                    $action = $oldBrokerId ? 'reassigned' : 'assigned';
                    // Send notification to the broker
                    $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, $user, $action));
                    // Send email to the seller
                    if ($sellerRequest->email) {
                        Mail::to($sellerRequest->email)
                            ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, $user));
                    }
                }
            }

            // Prepare outgoing emails for approve/reject actions
            $sendApprovedMail = false;
            $sendRejectedMail = false;
            $approvedMailProperty = null;
            $rejectionReason = $validated['rejection_reason'] ?? null;

            // Auto-convert to property if approved by broker or admin and not already converted
            if (
                in_array($validated['status'], ['approved']) &&
                !$sellerRequest->property_id &&
                in_array($user->role, ['admin', 'broker'])
            ) {
                // Compute lot area in sqm and price per sqm to satisfy properties table constraints
                $lotAreaSqm = null;
                
                // Try property_area first (old form field), then lot_area (new form field)
                $areaValue = $sellerRequest->property_area ?? $sellerRequest->lot_area;
                
                if ($areaValue) {
                    $areaVal = (float) $areaValue;
                    $unit = $sellerRequest->area_unit ?? 'sqm';
                    if (in_array($unit, ['sqm', 'sqm.'])) {
                        $lotAreaSqm = $areaVal;
                    } elseif (in_array($unit, ['hectares', 'hectare', 'ha'])) {
                        $lotAreaSqm = $areaVal * 10000;
                    } elseif (in_array($unit, ['acres', 'acre'])) {
                        $lotAreaSqm = $areaVal * 4046.8564224;
                    } else {
                        // fallback: treat as sqm
                        $lotAreaSqm = $areaVal;
                    }
                }

                $totalPrice = $sellerRequest->asking_price ?? null;
                $pricePerSqm = null;
                if ($totalPrice !== null && $lotAreaSqm > 0) {
                    $pricePerSqm = round($totalPrice / $lotAreaSqm, 2);
                }

                // Create property listing (robust against legacy schema without multi-type columns)
                // Handle property_type which can be a JSON array or single value
                $rawPropertyType = $sellerRequest->property_type;
                $propertyTypes = null; // optional multi-types if column exists
                $customTypeText = null; // optional custom type text if column exists

                // Normalize to array if JSON string
                if (is_string($rawPropertyType)) {
                    $decoded = null;
                    try {
                        $decoded = json_decode($rawPropertyType, true);
                    } catch (\Throwable $e) { $decoded = null; }
                    if (is_array($decoded)) {
                        $rawPropertyType = $decoded;
                    }
                }

                // Normalize memorial synonyms
                $normalizeSlug = function($val) {
                    if (!is_string($val)) return $val;
                    $slug = strtolower(trim($val));
                    $slug = str_replace([' ', '-'], '_', $slug);
                    if (in_array($slug, ['memorial', 'memorial_lot', 'memorial_park'])) {
                        return 'memorial_lot';
                    }
                    return $slug;
                };

                $hadOther = false;
                if (is_array($rawPropertyType)) {
                    // Capture list (will only be persisted if column exists)
                    $propertyTypes = array_values(array_filter(array_map($normalizeSlug, $rawPropertyType)));
                    // Detect if seller selected 'other' (case-insensitive in raw input)
                    foreach ($rawPropertyType as $v) {
                        if (is_string($v) && strtolower(trim($v)) === 'other') {
                            $hadOther = true;
                            break;
                        }
                    }
                    $singleType = $propertyTypes[0] ?? 'residential_lot';
                } else {
                    $singleType = $normalizeSlug($rawPropertyType ?: 'residential_lot');
                    if ($singleType === 'other') {
                        $hadOther = true;
                    }
                }

                // If seller explicitly used a custom type (Other + custom text), prefer storing as 'other' with custom text
                if ($hadOther && $sellerRequest->custom_property_type) {
                    $propertyTypes = ['other'];
                    $customTypeText = $sellerRequest->custom_property_type;
                    // Ensure singleType is a safe enum fallback
                    $singleType = 'residential_lot';
                }

                // Enforce enum compatibility for legacy 'type' column
                $validSingleTypes = [
                    'residential_lot','agricultural_land','commercial_lot','industrial_lot',
                    'beachfront','mountain_view','rice_field','coconut_plantation','subdivision_lot',
                    // legacy/extended enums present in migration
                    'titled_land','tax_declared','memorial_lot'
                ];
                if (!in_array($singleType, $validSingleTypes, true)) {
                    // Fallback to a safe default to avoid SQL enum exception
                    $singleType = 'residential_lot';
                }

                // Remove unsupported placeholders from multi-type list but allow 'other' for custom types
                if (is_array($propertyTypes)) {
                    $allowedMulti = array_merge($validSingleTypes, ['other']);
                    $propertyTypes = array_values(array_filter($propertyTypes, function($t) use ($allowedMulti) {
                        return in_array($t, $allowedMulti, true);
                    }));
                    if (empty($propertyTypes)) {
                        $propertyTypes = null; // avoid persisting empty array
                    }
                }

                // Validate title_type for Property ENUM constraint
                $validTitleTypes = ['titled', 'tax_declared', 'mother_title', 'cct'];
                $titleType = $sellerRequest->title_type;
                // If it's not a valid ENUM value, default to 'titled'
                if (!in_array($titleType, $validTitleTypes)) {
                    $titleType = 'titled';
                }
                // Build property payload, conditionally including new columns only if they exist
                // Determine broker ownership: prefer assigned broker; if approving as broker, use that broker; do not force admin as broker
                $brokerIdForProperty = $sellerRequest->assigned_broker_id ?? ($user->role === 'broker' ? $user->id : null);

                $propertyData = [
                    'slug' => Str::slug($sellerRequest->property_title . '-' . time()),
                    'title' => $sellerRequest->property_title,
                    'description' => $sellerRequest->property_description ?? '',
                    'type' => $singleType,
                    'status' => 'available',
                    'price_per_sqm' => $pricePerSqm ?? 0,
                    'total_price' => $totalPrice ?? 0,
                    'lot_area_sqm' => $lotAreaSqm ?? 0,
                    'lot_area_hectares' => $lotAreaSqm ? round($lotAreaSqm / 10000, 4) : 0,
                    'address' => $sellerRequest->address ?? '',
                    'municipality' => $sellerRequest->municipality ?? ($sellerRequest->city ?: 'Bohol'),
                    'barangay' => $sellerRequest->barangay ?? '',
                    'title_type' => $titleType,
                    'title_number' => $sellerRequest->title_number,
                    'coordinates_lat' => $sellerRequest->coordinates_lat,
                    'coordinates_lng' => $sellerRequest->coordinates_lng,
                    'nearby_landmarks' => $sellerRequest->nearby_landmarks,
                    'zoning_classification' => $sellerRequest->zoning_classification,
                    'road_access' => (bool)($sellerRequest->road_access ?? false),
                    'water_source' => (bool)($sellerRequest->water_source ?? false),
                    'electricity_available' => (bool)($sellerRequest->electricity_available ?? false),
                    'internet_available' => (bool)($sellerRequest->internet_available ?? false),
                    // images populated below after robust parse; avoid storing raw mixed types
                    'is_featured' => false,
                ];

                if ($brokerIdForProperty) {
                    $propertyData['broker_id'] = $brokerIdForProperty;
                }

                // Conditionally include multi-type / custom type columns only if schema supports them
                if (Schema::hasColumn('properties', 'types') && $propertyTypes) {
                    $propertyData['types'] = $propertyTypes;
                }
                if (Schema::hasColumn('properties', 'custom_type_text') && $customTypeText) {
                    $propertyData['custom_type_text'] = $customTypeText;
                }

                // Ensure images is an array (avoid double-encoded JSON bug)
                $parseArray = function($val) {
                    if (!$val) return [];
                    if (is_array($val)) return array_values(array_filter($val));
                    if (is_string($val)) {
                        $t = trim($val);
                        try {
                            $first = json_decode($t, true);
                            if (is_array($first)) return array_values(array_filter($first));
                            if (is_string($first) && str_starts_with($first, '[')) {
                                $second = json_decode($first, true);
                                if (is_array($second)) return array_values(array_filter($second));
                            }
                        } catch (\Throwable $e) { /* ignore */ }
                        return $t ? [$t] : [];
                    }
                    if (is_object($val)) return array_values(array_filter((array) $val));
                    return [];
                };

                $imagesArray = $parseArray($sellerRequest->uploaded_images) ?: $parseArray($sellerRequest->images);
                if (!empty($imagesArray)) {
                    $propertyData['images'] = $imagesArray;
                }

                $property = Property::create($propertyData);
                $sellerRequest->update([
                    'status' => 'listed',
                    'property_id' => $property->id,
                    'listed_at' => now()
                ]);

                // Mark to send approved mail
                $sendApprovedMail = true;
                $approvedMailProperty = $property;
            }

            DB::commit();

            // Send emails after commit to ensure data is persisted
            try {
                if ($validated['status'] === 'rejected' && $sellerRequest->email) {
                    Mail::to($sellerRequest->email)
                        ->send(new \App\Mail\SellerRequestRejectedMail($sellerRequest, $rejectionReason));
                }
                if ($sendApprovedMail && $sellerRequest->email) {
                    Mail::to($sellerRequest->email)
                        ->send(new \App\Mail\SellerRequestApprovedMail($sellerRequest, $approvedMailProperty));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send seller request status emails', [
                    'seller_request_id' => $sellerRequest->id,
                    'status' => $validated['status'],
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('seller-requests.show', $sellerRequest)
                ->with('message', 'Request status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update failed', [
                'seller_request_id' => $sellerRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'status' => $validated['status'] ?? 'unknown',
                'user_id' => $user->id
            ]);
            return back()->withErrors(['error' => 'Failed to update status. Please try again.']);
        }
    }

    /**
     * Convert approved request to property listing
     */
    public function convertToProperty(SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin' && $user->role !== 'broker') {
            abort(403, 'Only administrators and brokers can convert requests to properties.');
        }

        // Brokers can only convert their own assigned requests
        if ($user->role === 'broker' && $sellerRequest->broker_id !== $user->id) {
            abort(403, 'You can only convert seller requests assigned to you.');
        }

        if ($sellerRequest->property_id) {
            return back()->with('error', 'This request has already been converted to a property listing.');
        }

        try {
            DB::beginTransaction();

            $property = new Property();
            
            // Basic Information
            $property->broker_id = $sellerRequest->broker_id ?? $user->id;
            $property->title = $sellerRequest->property_title ?? $sellerRequest->address ?? 'Property from Seller Request';
            $property->description = $sellerRequest->property_description ?? $sellerRequest->additional_notes ?? '';
            $property->slug = Str::slug($property->title . '-' . time());
            
            // Property Types - Handle both array and string
            $types = $sellerRequest->property_type;
            if (is_string($types)) {
                try {
                    $decoded = json_decode($types, true);
                    $property->types = is_array($decoded) ? $decoded : [$types];
                } catch (\Exception $e) {
                    $property->types = [$types];
                }
            } elseif (is_array($types)) {
                $property->types = $types;
            } else {
                $property->types = ['residential_lot']; // default
            }
            
            // Custom type if exists
            if ($sellerRequest->custom_property_type) {
                $property->custom_type_text = $sellerRequest->custom_property_type;
            }

            // Location - THIS IS THE FIX FOR MISSING LOCATION
            $property->municipality = $sellerRequest->municipality ?? 'Bohol';
            $property->barangay = $sellerRequest->barangay ?? '';
            $property->address = $sellerRequest->address ?? '';
            $property->zoning_classification = $sellerRequest->zoning_classification ?? null;
            
            // Nearby landmarks - handle both string and array
            if ($sellerRequest->nearby_landmarks) {
                if (is_string($sellerRequest->nearby_landmarks)) {
                    $property->nearby_landmarks = array_map('trim', explode(',', $sellerRequest->nearby_landmarks));
                } else {
                    $property->nearby_landmarks = $sellerRequest->nearby_landmarks;
                }
            }

            // Pricing & Area - THIS IS THE FIX FOR MISSING SQM AND PRICE PER SQM
            $lotArea = $sellerRequest->lot_area_sqm ?? $sellerRequest->lot_area ?? $sellerRequest->property_area ?? 0;
            
            // Handle area unit conversion if needed
            if ($sellerRequest->area_unit) {
                $areaVal = (float) $lotArea;
                $unit = $sellerRequest->area_unit;
                if (in_array($unit, ['hectares', 'hectare', 'ha'])) {
                    $lotArea = $areaVal * 10000;
                } elseif (in_array($unit, ['acres', 'acre'])) {
                    $lotArea = $areaVal * 4046.8564224;
                }
            }
            
            $totalPrice = $sellerRequest->asking_price ?? $sellerRequest->price_expectation ?? 0;
            
            $property->lot_area_sqm = $lotArea;
            $property->lot_area_hectares = $lotArea > 0 ? round($lotArea / 10000, 4) : null;
            $property->total_price = $totalPrice;
            
            // Calculate price per sqm
            if ($lotArea > 0 && $totalPrice > 0) {
                $property->price_per_sqm = round($totalPrice / $lotArea, 2);
            } else {
                $property->price_per_sqm = null;
            }

            // Title Information
            $property->title_type = $sellerRequest->title_type ?? 'titled';
            $property->title_number = $sellerRequest->title_number ?? null;
            
            // Features/Amenities - parse from seller request
            $features = [];
            if ($sellerRequest->features) {
                if (is_string($sellerRequest->features)) {
                    try {
                        $features = json_decode($sellerRequest->features, true) ?? [];
                    } catch (\Exception $e) {
                        $features = [];
                    }
                } elseif (is_array($sellerRequest->features)) {
                    $features = $sellerRequest->features;
                }
            }
            
            // Map features to property amenities
            $property->road_access = in_array('road_access', $features) || $sellerRequest->road_access;
            $property->electricity_available = in_array('electricity', $features) || $sellerRequest->electricity || $sellerRequest->electricity_available;
            $property->water_source = in_array('water_source', $features) || $sellerRequest->water_source;
            $property->internet_available = in_array('internet', $features) || $sellerRequest->internet || $sellerRequest->internet_available;

            // GIS/Coordinates
            $property->coordinates_lat = $sellerRequest->coordinates_lat ?? null;
            $property->coordinates_lng = $sellerRequest->coordinates_lng ?? null;

            // Property Status
            $property->status = 'draft'; // Start as draft for broker to review
            $property->is_featured = false;
            
            $property->save();

            // Transfer Images
            $images = $sellerRequest->images ?? $sellerRequest->uploaded_images ?? [];
            if ($images && is_array($images)) {
                $transferredImages = [];
                
                foreach ($images as $sellerImage) {
                    // Get the original image path
                    $originalPath = str_replace('/storage/', '', $sellerImage);
                    
                    if (Storage::disk('public')->exists($originalPath)) {
                        // Create new filename for property
                        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                        $newFilename = 'properties/' . Str::uuid() . '.' . $extension;
                        
                        // Copy the image to property directory
                        Storage::disk('public')->copy($originalPath, $newFilename);
                        
                        $transferredImages[] = $newFilename;
                    }
                }
                
                if (!empty($transferredImages)) {
                    $property->images = $transferredImages;
                    $property->save();
                }
            }

            // Transfer Documents (property_documents)
            if ($sellerRequest->property_documents && is_array($sellerRequest->property_documents)) {
                $transferredDocs = [];
                
                foreach ($sellerRequest->property_documents as $doc) {
                    $originalPath = str_replace('/storage/', '', $doc);
                    
                    if (Storage::disk('public')->exists($originalPath)) {
                        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                        $newFilename = 'property-documents/' . Str::uuid() . '.' . $extension;
                        
                        Storage::disk('public')->copy($originalPath, $newFilename);
                        $transferredDocs[] = $newFilename;
                    }
                }
                
                if (!empty($transferredDocs)) {
                    $property->documents = $transferredDocs;
                    $property->save();
                }
            }

            // Link seller request to property
            $sellerRequest->property_id = $property->id;
            $sellerRequest->status = 'listed';
            $sellerRequest->listed_at = now();
            $sellerRequest->save();

            DB::commit();

            // Send notification to seller (if user exists)
            if ($sellerRequest->user_id) {
                $seller = User::find($sellerRequest->user_id);
                if ($seller) {
                    // $seller->notify(new \App\Notifications\SellerRequestConverted($sellerRequest, $property));
                }
            }

            return redirect()
                ->route('broker.properties.show', $property)
                ->with('success', 'Seller request converted to property listing successfully! You can now review and publish it.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property conversion failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to convert to property. Please try again.']);
        }
    }

    /**
     * Show success page after submission
     */
    public function success(Request $request)
    {
        $sellerRequestId = $request->query('id');
        
        if ($sellerRequestId) {
            $sellerRequest = SellerRequest::find($sellerRequestId);
            
            if ($sellerRequest) {
                return Inertia::render('SellerRequests/Success', [
                    'sellerRequest' => $sellerRequest,
                    'estimatedResponseTime' => '2-3 business days'
                ]);
            }
        }
        
        // If no ID provided or request not found, show generic success
        return Inertia::render('SellerRequests/Success', [
            'sellerRequest' => null,
            'estimatedResponseTime' => '2-3 business days'
        ]);
    }

    /**
     * Assign broker to seller request
     */
    public function assign(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can assign brokers to seller requests.');
        }

        $validated = $request->validate([
            'seller_request_id' => 'required|exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $sellerRequest = SellerRequest::findOrFail($validated['seller_request_id']);
        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->firstOrFail();

        $wasAssigned = $sellerRequest->assigned_broker_id !== null;
        $action = $wasAssigned ? 'reassigned' : 'assigned';

        $sellerRequest->update([
            'assigned_broker_id' => $broker->id,
        ]);

        // Send notification to the broker
        $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, Auth::user(), $action));

        // Send email to the seller
        if ($sellerRequest->seller_email) {
            Mail::to($sellerRequest->seller_email)
                ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, Auth::user()));
        }

        return redirect()->back()->with('success', 'Broker assigned successfully to seller request.');
    }

    /**
     * Bulk assign broker to multiple seller requests
     */
    public function bulkAssign(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can bulk assign brokers to seller requests.');
        }

        $validated = $request->validate([
            'seller_request_ids' => 'required|array|min:1',
            'seller_request_ids.*' => 'exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->firstOrFail();

        // Get seller requests before updating to check assignment status
        $sellerRequests = SellerRequest::whereIn('id', $validated['seller_request_ids'])->get();

        SellerRequest::whereIn('id', $validated['seller_request_ids'])
            ->update(['assigned_broker_id' => $broker->id]);

        // Send notifications for each seller request
        foreach ($sellerRequests as $sellerRequest) {
            $wasAssigned = $sellerRequest->assigned_broker_id !== null;
            $action = $wasAssigned ? 'reassigned' : 'assigned';

            // Send notification to the broker
            $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, Auth::user(), $action));

            // Send email to the seller
            if ($sellerRequest->seller_email) {
                Mail::to($sellerRequest->seller_email)
                    ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, Auth::user()));
            }
        }

        $count = count($validated['seller_request_ids']);
        return redirect()->back()->with('success', "Successfully assigned {$count} seller requests to {$broker->name}.");
    }

    /**
     * Get broker performance analytics for seller requests
     */
    public function getBrokerAnalytics(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view broker analytics.');
        }

        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'assignedSellerRequests',
                'assignedSellerRequests as pending_seller_requests_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'assignedSellerRequests as approved_seller_requests_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'assignedSellerRequests as listed_properties_count' => function ($query) {
                    $query->whereNotNull('property_id');
                }
            ])
            ->orderByDesc('assigned_seller_requests_count')
            ->get()
            ->map(function ($broker) {
                $listingRate = $broker->assigned_seller_requests_count > 0 
                    ? round(($broker->listed_properties_count / $broker->assigned_seller_requests_count) * 100, 1)
                    : 0;

                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'total_seller_requests' => $broker->assigned_seller_requests_count,
                    'pending_requests' => $broker->pending_seller_requests_count,
                    'approved_requests' => $broker->approved_seller_requests_count,
                    'listed_properties' => $broker->listed_properties_count,
                    'listing_rate' => $listingRate,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brokers,
        ]);
    }

    /**
     * Get assignment recommendations based on broker workload and performance for seller requests
     */
    public function getAssignmentRecommendations(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view assignment recommendations.');
        }

        $validated = $request->validate([
            'seller_request_id' => 'required|exists:seller_requests,id',
        ]);

        $sellerRequest = SellerRequest::findOrFail($validated['seller_request_id']);

        // Get brokers with their current workload and performance metrics
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'assignedSellerRequests',
                'assignedSellerRequests as pending_seller_requests_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'assignedSellerRequests as listed_properties_count' => function ($query) {
                    $query->whereNotNull('property_id');
                }
            ])
            ->get()
            ->map(function ($broker) use ($sellerRequest) {
                // Calculate recommendation score based on:
                // 1. Current workload (lower is better)
                // 2. Performance history (listing success rate)
                // 3. Location proximity (if available)
                
                $workloadScore = max(0, 100 - ($broker->pending_seller_requests_count * 15)); // Penalize high workload
                $performanceScore = $broker->assigned_seller_requests_count > 0 
                    ? ($broker->listed_properties_count / $broker->assigned_seller_requests_count) * 100
                    : 50; // Default score for new brokers
                
                $totalScore = ($workloadScore * 0.6) + ($performanceScore * 0.4);
                
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'current_seller_requests' => $broker->assigned_seller_requests_count,
                    'pending_requests' => $broker->pending_seller_requests_count,
                    'listed_properties' => $broker->listed_properties_count,
                    'recommendation_score' => round($totalScore, 1),
                    'workload_level' => $this->getWorkloadLevel($broker->pending_seller_requests_count),
                ];
            })
            ->sortByDesc('recommendation_score')
            ->values();

        return response()->json([
            'success' => true,
            'sellerRequest' => $sellerRequest,
            'recommendations' => $brokers,
        ]);
    }

    /**
     * Get workload level description for seller requests
     */
    private function getWorkloadLevel($pendingRequests)
    {
        if ($pendingRequests <= 3) {
            return 'Light';
        } elseif ($pendingRequests <= 6) {
            return 'Moderate';
        } elseif ($pendingRequests <= 10) {
            return 'Heavy';
        } else {
            return 'Overloaded';
        }
    }

    /**
     * Filter an associative array to only include keys that exist as columns
     * on the seller_requests table. This prevents SQL exceptions when older
     * deployments lack newer columns.
     */
    private function filterExistingColumns(array $data): array
    {
        $filtered = [];
        foreach ($data as $key => $value) {
            try {
                if (Schema::hasColumn('seller_requests', $key)) {
                    $filtered[$key] = $value;
                } else {
                    Log::warning('Skipping non-existent seller_requests column during update', ['column' => $key]);
                }
            } catch (Exception $e) {
                // If schema check fails for any reason, skip the column but log it
                Log::warning('Schema check failed when filtering columns for seller_requests', ['column' => $key, 'error' => $e->getMessage()]);
            }
        }

        return $filtered;
    }

    /**
     * Smart auto-assignment algorithm
     * Assigns broker based on location, workload, and experience
     */
    protected function autoAssignBroker(SellerRequest $sellerRequest): ?User
    {
        return User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->where('prc_verified', true)
            ->whereNull('suspended_at')
            // Prefer brokers in same municipality
            ->when($sellerRequest->city, function($q) use ($sellerRequest) {
                $q->where('city', $sellerRequest->city);
            })
            // Consider current workload
            ->withCount(['assignedSellerRequests as pending_count' => function($q) {
                $q->whereIn('status', ['pending', 'under_review', 'approved']);
            }])
            // Prioritize less busy brokers
            ->orderBy('pending_count', 'asc')
            // Then by experience
            ->orderBy('years_experience', 'desc')
            // Get the best match
            ->first();
    }
}