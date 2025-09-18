<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\Api\PropertyStoreRequest;
use App\Http\Resources\PropertyResource;
use App\Services\FileSecurityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PropertyController extends Controller
{
    /**
     * Get all properties (public)
     */
    public function index(Request $request)
    {
        $query = Property::with(['broker', 'transactions'])
            ->where('status', 'available')
            ->where('is_featured', true);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->has('bathrooms')) {
            $query->where('bathrooms', $request->bathrooms);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties->items()),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    /**
     * Get single property (public)
     */
    public function show($id)
    {
        try {
            $property = Property::with(['broker', 'transactions', 'inquiries'])
                ->where('status', 'available')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new PropertyResource($property)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Search properties
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'per_page' => 'integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $searchTerm = $request->get('q');
        $perPage = $request->get('per_page', 15);

        $properties = Property::with(['broker'])
            ->where('status', 'available')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('municipality', 'like', '%' . $searchTerm . '%')
                    ->orWhere('type', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties->items()),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    /**
     * Get featured properties
     */
    public function featured(Request $request)
    {
        $limit = min($request->get('limit', 6), 20); // Max 20 featured properties

        $properties = Property::with(['broker'])
            ->where('status', 'available')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties)
        ]);
    }

    /**
     * Get broker's properties
     */
    public function brokerProperties(Request $request)
    {
        $query = Property::with(['transactions', 'inquiries'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = min($request->get('per_page', 15), 50);
        $properties = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Get single property for broker
     */
    public function brokerShow($id)
    {
        try {
            $property = Property::with(['transactions', 'inquiries'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $property
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created property
     */
    public function store(PropertyStoreRequest $request)
    {
        try {
            // Handle image uploads with security
            $imagePaths = [];
            if ($request->hasFile('images')) {
                $fileSecurityService = app(FileSecurityService::class);
                
                foreach ($request->file('images') as $image) {
                    // Validate file security
                    $fileSecurityService->validateFile($image);
                    
                    $imagePaths[] = $image->store('properties', 'public');
                }
            }

            $property = Property::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'property_type' => $request->property_type,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'area' => $request->area,
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'images' => json_encode($imagePaths),
                'amenities' => json_encode($request->amenities ?? []),
                'broker_id' => $request->user()->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => new PropertyResource($property->load('user'))
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update property
     */
    public function update(PropertyFileUploadRequest $request, $id)
    {
        try {
            $property = Property::where('user_id', Auth::id())->findOrFail($id);
            
            $validatedData = $request->validated();
            
            // Handle file uploads securely if new files are provided
            if ($request->hasFile(['images', 'documents', 'virtual_tour_images'])) {
                $fileData = $request->storeFilesSecurely();
                $validatedData = array_merge($validatedData, $fileData);
            }

            $property->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->load(['user'])
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete property
     */
    public function destroy($id)
    {
        try {
            $property = Property::where('user_id', Auth::id())->findOrFail($id);
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get all properties for admin
     */
    public function adminProperties(Request $request)
    {
        $query = Property::with(['user', 'transactions', 'inquiries']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $properties = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Approve property
     */
    public function approve($id)
    {
        try {
            $property = Property::findOrFail($id);
            $property->update(['status' => 'approved']);

            return response()->json([
                'success' => true,
                'message' => 'Property approved successfully',
                'data' => $property
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Reject property
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $property = Property::findOrFail($id);
            $property->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property rejected successfully',
                'data' => $property
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}