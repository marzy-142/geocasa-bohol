<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\Api\InquiryStoreRequest;
use App\Http\Resources\InquiryResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InquiryController extends Controller
{
    /**
     * Get user's inquiries
     */
    public function index(Request $request)
    {
        $query = Inquiry::with(['property', 'property.user'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $inquiries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => InquiryResource::collection($inquiries->items()),
            'meta' => [
                'current_page' => $inquiries->currentPage(),
                'last_page' => $inquiries->lastPage(),
                'per_page' => $inquiries->perPage(),
                'total' => $inquiries->total(),
            ]
        ]);
    }

    /**
     * Create new inquiry
     */
    public function store(InquiryStoreRequest $request)
    {

        try {
            // Check if property exists and is available
            $property = Property::where('id', $request->property_id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->firstOrFail();

            // Check if user already has a pending inquiry for this property
            $existingInquiry = Inquiry::where('user_id', Auth::id())
                ->where('property_id', $request->property_id)
                ->where('status', 'pending')
                ->first();

            if ($existingInquiry) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending inquiry for this property'
                ], Response::HTTP_CONFLICT);
            }

            $inquiry = Inquiry::create([
                'user_id' => Auth::id(),
                'property_id' => $request->property_id,
                'message' => $request->message,
                'phone' => $request->phone,
                'preferred_contact_time' => $request->preferred_contact_time,
                'inquiry_type' => $request->inquiry_type ?? 'information',
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry submitted successfully',
                'data' => new InquiryResource($inquiry->load(['property', 'property.user']))
            ], Response::HTTP_CREATED);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or not available'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit inquiry',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single inquiry
     */
    public function show($id)
    {
        try {
            $inquiry = Inquiry::with(['property', 'property.user', 'user'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new InquiryResource($inquiry)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inquiry not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get broker's inquiries
     */
    public function brokerInquiries(Request $request)
    {
        $query = Inquiry::with(['property', 'user'])
            ->whereHas('property', function ($q) {
                $q->where('user_id', Auth::id());
            });

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $inquiries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inquiries
        ]);
    }

    /**
     * Update inquiry status (broker only)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,responded,closed',
            'response_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $inquiry = Inquiry::with(['property'])
                ->whereHas('property', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->findOrFail($id);

            $updateData = [
                'status' => $request->status,
            ];

            if ($request->has('response_message')) {
                $updateData['response_message'] = $request->response_message;
                $updateData['responded_at'] = now();
            }

            $inquiry->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry status updated successfully',
                'data' => $inquiry->load(['property', 'user'])
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inquiry not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inquiry status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all inquiries for admin
     */
    public function adminInquiries(Request $request)
    {
        $query = Inquiry::with(['property', 'property.user', 'user']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $inquiries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inquiries
        ]);
    }
}