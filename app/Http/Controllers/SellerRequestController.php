<?php

namespace App\Http\Controllers;

use App\Models\SellerRequest;
use App\Models\Property;
use App\Models\User;
use App\Http\Requests\SellerRequestUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Notifications\SellerRequestNotification;
use App\Notifications\BrokerSellerAssignmentNotification;
use App\Mail\SellerBrokerAssignedMail;

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
                $q->where('seller_name', 'like', "%{$search}%")
                  ->orWhere('seller_email', 'like', "%{$search}%")
                  ->orWhere('property_title', 'like', "%{$search}%")
                  ->orWhere('property_location', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
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
            'filters' => $request->only(['search', 'status', 'assignment_status', 'broker_id', 'date_from', 'date_to', 'property_type', 'price_min', 'price_max']),
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

        return Inertia::render('SellerRequests/Create', [
            'availableFeatures' => $availableFeatures
        ]);
    }

    /**
     * Store a newly created seller request with enhanced validation
     */
    public function store(SellerRequestUploadRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle secure file uploads using the enhanced security service
            $storedFiles = $request->storeFilesSecurely([
                'uploaded_images',
                'property_documents',
                'ownership_documents'
            ], 'seller-requests', 'public');
            
            // Add stored file paths to validated data
            if (isset($storedFiles['uploaded_images'])) {
                $validated['uploaded_images'] = $storedFiles['uploaded_images'];
            }
            
            if (isset($storedFiles['property_documents'])) {
                $validated['property_documents'] = $storedFiles['property_documents'];
            }
            
            if (isset($storedFiles['ownership_documents'])) {
                $validated['ownership_documents'] = $storedFiles['ownership_documents'];
            }

            // Create seller request
            $sellerRequest = SellerRequest::create($validated);

            // Send notifications asynchronously
            $this->sendNotifications($sellerRequest);

            DB::commit();

            return redirect()->route('seller-requests.success', ['id' => $sellerRequest->id])
                ->with('success', 'Your property listing request has been submitted successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Seller request creation failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }

    /**
     * Send notifications to admins and brokers
     */
    private function sendNotifications(SellerRequest $sellerRequest)
    {
        try {
            $admins = User::where('role', 'admin')->get();
            $brokers = User::where('role', 'broker')->where('is_approved', true)->get();
            
            $recipients = $admins->merge($brokers);
            
            foreach ($recipients as $recipient) {
                $recipient->notify(new SellerRequestNotification($sellerRequest));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications: ' . $e->getMessage());
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
            'property_type' => 'required|in:residential,commercial,agricultural,industrial,recreational',
            'features' => 'nullable|array',
            'status' => 'required|in:pending,under_review,approved,rejected,listed',
            'admin_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
            'assigned_broker_id' => 'nullable|exists:users,id'
        ]);

        // Only admins can change status and assignment
        if ($user->role !== 'admin') {
            unset($validated['status'], $validated['assigned_broker_id']);
        }

        $sellerRequest->update($validated);

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
            'status' => 'required|in:pending,under_review,approved,rejected,listed',
            'admin_notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:500|required_if:status,rejected',
            'assigned_broker_id' => 'nullable|exists:users,id'
        ]);

        // Validate broker assignment
        if ($validated['assigned_broker_id']) {
            $broker = User::find($validated['assigned_broker_id']);
            if (!$broker || $broker->role !== 'broker' || !$broker->is_approved) {
                return back()->withErrors(['assigned_broker_id' => 'Invalid broker selection.']);
            }
        }

        try {
            DB::beginTransaction();

            $oldBrokerId = $sellerRequest->assigned_broker_id;
            $newBrokerId = $validated['assigned_broker_id'] ?? $sellerRequest->assigned_broker_id;

            $sellerRequest->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
                'assigned_broker_id' => $newBrokerId,
                'reviewed_by' => $user->id,
                'reviewed_at' => now()
            ]);

            // Send notifications if broker assignment changed
            if ($newBrokerId && $oldBrokerId !== $newBrokerId) {
                $broker = User::find($newBrokerId);
                if ($broker) {
                    $action = $oldBrokerId ? 'reassigned' : 'assigned';
                    
                    // Send notification to the broker
                    $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, $user, $action));
                    
                    // Send email to the seller
                    if ($sellerRequest->seller_email) {
                        Mail::to($sellerRequest->seller_email)
                            ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, $user));
                    }
                }
            }

            DB::commit();

            return redirect()->route('seller-requests.show', $sellerRequest)
                ->with('message', 'Request status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update status. Please try again.']);
        }
    }

    /**
     * Convert approved request to property listing
     */
    public function convertToProperty(SellerRequest $sellerRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can convert requests to properties.');
        }

        if ($sellerRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be converted to property listings.');
        }

        if ($sellerRequest->property_id) {
            return back()->with('error', 'This request has already been converted to a property listing.');
        }

        try {
            DB::beginTransaction();

            $property = Property::create([
                'title' => $sellerRequest->property_title,
                'description' => $sellerRequest->property_description,
                'type' => $sellerRequest->property_type,
                'status' => 'available',
                'price' => $sellerRequest->asking_price,
                'area' => $sellerRequest->property_area,
                'area_unit' => $sellerRequest->area_unit,
                'location' => $sellerRequest->property_location,
                'address' => $sellerRequest->property_address,
                'city' => $sellerRequest->city,
                'state' => $sellerRequest->state,
                'zip_code' => $sellerRequest->zip_code,
                'latitude' => $sellerRequest->latitude,
                'longitude' => $sellerRequest->longitude,
                'features' => $sellerRequest->features,
                'images' => $sellerRequest->uploaded_images,
                'broker_id' => $sellerRequest->assigned_broker_id ?? $user->id,
                'is_featured' => false
            ]);

            $sellerRequest->update([
                'status' => 'listed',
                'property_id' => $property->id,
                'listed_at' => now()
            ]);

            DB::commit();

            return redirect()->route('broker.properties.show', $property)
                ->with('message', 'Seller request has been successfully converted to a property listing.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property conversion failed: ' . $e->getMessage());
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
}