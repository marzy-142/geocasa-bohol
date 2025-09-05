<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerRequest;
use App\Http\Requests\SellerRequestUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\SellerRequestResource;
use App\Services\FileSecurityService;

class SellerRequestController extends Controller
{
    /**
     * Get broker's seller requests
     */
    public function index(Request $request)
    {
        $query = SellerRequest::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $sellerRequests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SellerRequestResource::collection($sellerRequests->items()),
            'meta' => [
                'current_page' => $sellerRequests->currentPage(),
                'last_page' => $sellerRequests->lastPage(),
                'per_page' => $sellerRequests->perPage(),
                'total' => $sellerRequests->total(),
            ]
        ]);
    }

    /**
     * Create new seller request (public)
     */
    public function store(SellerRequestUploadRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['status'] = 'pending';

            // Handle file uploads with security
            $fileSecurityService = app(FileSecurityService::class);
            $fileData = $request->storeFilesSecurely($fileSecurityService);
            $validatedData = array_merge($validatedData, $fileData);

            $sellerRequest = SellerRequest::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Seller request submitted successfully',
                'data' => new SellerRequestResource($sellerRequest->load('assignedBroker'))
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit seller request',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single seller request
     */
    public function show($id)
    {
        try {
            $sellerRequest = SellerRequest::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new SellerRequestResource($sellerRequest)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seller request not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update seller request status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,under_review,approved,rejected,contacted',
            'notes' => 'nullable|string|max:1000',
            'broker_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $sellerRequest = SellerRequest::findOrFail($id);
            
            $updateData = [
                'status' => $request->status,
            ];

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            if ($request->has('broker_id')) {
                $updateData['assigned_broker_id'] = $request->broker_id;
            }

            // Set processed timestamp when status changes from pending
            if ($sellerRequest->status === 'pending' && $request->status !== 'pending') {
                $updateData['processed_at'] = now();
                $updateData['processed_by'] = Auth::id();
            }

            $sellerRequest->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Seller request status updated successfully',
                'data' => $sellerRequest->fresh()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seller request not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update seller request status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all seller requests for admin
     */
    public function adminIndex(Request $request)
    {
        $query = SellerRequest::with(['assignedBroker', 'processedBy']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('assigned_broker_id')) {
            $query->where('assigned_broker_id', $request->assigned_broker_id);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('owner_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('owner_email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('owner_phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('asking_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('asking_price', '<=', $request->max_price);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $sellerRequests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SellerRequestResource::collection($sellerRequests->items()),
            'meta' => [
                'current_page' => $sellerRequests->currentPage(),
                'last_page' => $sellerRequests->lastPage(),
                'per_page' => $sellerRequests->perPage(),
                'total' => $sellerRequests->total(),
            ]
        ]);
    }

    /**
     * Get seller request statistics
     */
    public function statistics(Request $request)
    {
        $query = SellerRequest::query();

        // Apply date filter if provided
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $statistics = [
            'total_requests' => $query->count(),
            'pending_requests' => $query->where('status', 'pending')->count(),
            'under_review_requests' => $query->where('status', 'under_review')->count(),
            'approved_requests' => $query->where('status', 'approved')->count(),
            'rejected_requests' => $query->where('status', 'rejected')->count(),
            'contacted_requests' => $query->where('status', 'contacted')->count(),
        ];

        // Property type breakdown
        $propertyTypes = $query->select('property_type')
            ->selectRaw('count(*) as count')
            ->groupBy('property_type')
            ->pluck('count', 'property_type')
            ->toArray();

        // Monthly breakdown for the current year
        $monthlyData = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months with 0
        $monthlyBreakdown = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyBreakdown[$i] = $monthlyData[$i] ?? 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $statistics,
                'property_types' => $propertyTypes,
                'monthly_breakdown' => $monthlyBreakdown
            ]
        ]);
    }

    /**
     * Assign broker to seller request
     */
    public function assignBroker(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'broker_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // Verify the broker exists and has broker role
            $broker = \App\Models\User::where('id', $request->broker_id)
                ->where('role', 'broker')
                ->firstOrFail();

            $sellerRequest = SellerRequest::findOrFail($id);
            
            $sellerRequest->update([
                'assigned_broker_id' => $request->broker_id,
                'status' => 'under_review',
                'assigned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Broker assigned successfully',
                'data' => $sellerRequest->load('assignedBroker')
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seller request or broker not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign broker',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}