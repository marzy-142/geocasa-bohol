<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimpleSellerRequestRequest;
use App\Models\SellerRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SellerRequestController extends Controller
{
    /**
     * Show the form for creating a new seller request
     */
    public function create()
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
        }
        
        // Get available features (same as public form for consistency)
        $availableFeatures = [
            'Swimming Pool',
            'Garden',
            'Parking',
            'Security',
            'Furnished',
            'Air Conditioning',
            'Balcony',
            'Terrace',
            'Fireplace',
            'Storage',
            'Laundry Room',
            'Gym',
            'Playground',
            'Near Beach',
            'Mountain View',
            'City View',
            'Gated Community',
            'Pet Friendly',
            'Solar Panels',
        ];
        
        // Get available verified brokers for selection (same as public form)
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
        
        // Use the same comprehensive form component as public route
        // Pass client prop to enable pre-filling of contact information
        return Inertia::render('SellerRequests/Create', [
            'client' => $client,
            'availableFeatures' => $availableFeatures,
            'availableBrokers' => $availableBrokers,
            'municipalities' => \App\Models\Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    /**
     * Store a newly created seller request
     */
    public function store(SimpleSellerRequestRequest $request)
    {
        try {
            DB::beginTransaction();

            $sellerRequest = new SellerRequest();
            
            // Map contact_* fields to name/email/phone in database
            $sellerRequest->name = $request->contact_name;
            $sellerRequest->email = $request->contact_email;
            $sellerRequest->phone = $request->contact_phone;
            
            // Property Information
            $sellerRequest->property_title = $request->property_title;
            $sellerRequest->property_description = $request->property_description;
            
            // Store property types as JSON array
            $sellerRequest->property_type = $request->property_type;
            
            // Custom property type
            if (in_array('other', $request->property_type ?? []) && $request->custom_property_type) {
                $sellerRequest->custom_property_type = $request->custom_property_type;
            }
            
            // Pricing & Area - map lot_area_sqm to lot_area
            $sellerRequest->asking_price = $request->asking_price;
            $sellerRequest->lot_area = $request->lot_area_sqm;
            $sellerRequest->lot_area_sqm = $request->lot_area_sqm;
            $sellerRequest->price_expectation = $request->price_expectation;
            
            // Location
            $sellerRequest->municipality = $request->municipality;
            $sellerRequest->barangay = $request->barangay;
            $sellerRequest->address = $request->address;
            $sellerRequest->nearby_landmarks = $request->nearby_landmarks;
            
            // Title Information
            $sellerRequest->title_type = $request->title_type;
            $sellerRequest->title_number = $request->title_number;
            $sellerRequest->zoning_classification = $request->zoning_classification;
            
            // Features
            $sellerRequest->features = $request->features ?? [];
            
            // GIS
            $sellerRequest->coordinates_lat = $request->coordinates_lat;
            $sellerRequest->coordinates_lng = $request->coordinates_lng;
            
            // Additional Information
            $sellerRequest->additional_notes = $request->additional_notes;
            $sellerRequest->urgency_level = $request->urgency_level ?? 'medium';
            $sellerRequest->preferred_contact_method = $request->preferred_contact_method ?? 'both';
            $sellerRequest->best_time_to_contact = $request->best_time_to_contact;
            
            // Consent
            $sellerRequest->marketing_consent = $request->marketing_consent ?? false;
            // Newsletter consent deprecated; ignore any incoming value for backward compatibility
            
            // Broker
            $sellerRequest->broker_selection_method = $request->broker_selection_method ?? 'manual';
            if ($request->preferred_broker_id) {
                $sellerRequest->broker_id = $request->preferred_broker_id;
            }
            $sellerRequest->status = 'pending';
            
            // User & Client linkage for "My Listing Requests"
            $sellerRequest->user_id = auth()->check() ? auth()->id() : null;

            // Ensure seller request is linked to the authenticated client's record
            if (auth()->check()) {
                $user = auth()->user();
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

                // Link the request to client so it appears in Client/SellerRequests index
                $sellerRequest->client_id = $client->id;
            }
            
            // Upload property_images (changed from uploaded_images)
            $imagesPaths = [];
            if ($request->hasFile('property_images')) {
                foreach ($request->file('property_images') as $image) {
                    $path = $image->store('seller-requests/images', 'public');
                    $imagesPaths[] = $path;
                }
            }
            $sellerRequest->images = $imagesPaths;
            
            // Property Documents
            $propertyDocsPaths = [];
            if ($request->hasFile('property_documents')) {
                foreach ($request->file('property_documents') as $doc) {
                    $path = $doc->store('seller-requests/property-documents', 'public');
                    $propertyDocsPaths[] = $path;
                }
            }
            $sellerRequest->property_documents = $propertyDocsPaths;
            
            // Ownership Documents
            $ownershipDocsPaths = [];
            if ($request->hasFile('ownership_documents')) {
                foreach ($request->file('ownership_documents') as $doc) {
                    $path = $doc->store('seller-requests/ownership-documents', 'public');
                    $ownershipDocsPaths[] = $path;
                }
            }
            $sellerRequest->ownership_documents = $ownershipDocsPaths;
            
            $sellerRequest->save();

            DB::commit();

            if (auth()->check()) {
                return redirect()
                    ->route('client.dashboard')
                    ->with('success', 'Your property listing request has been submitted successfully!');
            } else {
                return redirect()
                    ->route('home')
                    ->with('success', 'Thank you! Your property listing request has been submitted.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating seller request: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Display client's seller requests
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client) {
            return Inertia::render('Client/SellerRequests', [
                'sellerRequests' => [],
            ]);
        }

        // Get client's seller requests with relationships
        $sellerRequests = SellerRequest::where('client_id', $client->id)
            ->with([
                'assignedBroker:id,name,email,phone',
                'property:id,title,slug,status,total_price',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'property_type' => $request->property_type,
                    'address' => $request->address,
                    'municipality' => $request->municipality,
                    'price_expectation' => $request->price_expectation,
                    'status' => $request->status,
                    'submission_date' => $request->submission_date,
                    'assigned_broker' => $request->assignedBroker ? [
                        'id' => $request->assignedBroker->id,
                        'name' => $request->assignedBroker->name,
                        'email' => $request->assignedBroker->email,
                        'phone' => $request->assignedBroker->phone,
                    ] : null,
                    'property' => $request->property ? [
                        'id' => $request->property->id,
                        'title' => $request->property->title,
                        'slug' => $request->property->slug,
                        'status' => $request->property->status,
                        'total_price' => $request->property->total_price,
                    ] : null,
                    'created_at' => $request->created_at,
                ];
            });

        return Inertia::render('Client/SellerRequests', [
            'sellerRequests' => $sellerRequests,
        ]);
    }

    /**
     * Display the specified seller request
     */
    public function show(SellerRequest $sellerRequest)
    {
        $user = auth()->user();
        
        // Get client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        // Verify this seller request belongs to the client
        if (!$client || $sellerRequest->client_id !== $client->id) {
            abort(403, 'Unauthorized access to seller request');
        }

        $sellerRequest->load([
            'assignedBroker:id,name,email,phone',
            'property:id,title,slug,status,total_price,main_image',
        ]);

        return Inertia::render('Client/SellerRequestShow', [
            'sellerRequest' => $sellerRequest,
        ]);
    }
}
