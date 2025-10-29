<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
        
        return Inertia::render('Client/SellProperty', [
            'client' => $client,
            'municipalities' => \App\Models\Property::BOHOL_MUNICIPALITIES,
            'propertyTypes' => \App\Models\Property::TYPES,
        ]);
    }

    /**
     * Store a newly created seller request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_type' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'municipality' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'lot_area' => 'required|numeric|min:0',
            'price_expectation' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'images.*' => 'nullable|image|max:5120', // 5MB max per image
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max per document
        ]);

        try {
            DB::beginTransaction();

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
                    'phone' => $validated['contact_phone'],
                ]);
            }

            // Auto-generate property title for land
            $propertyTitle = number_format($validated['lot_area'], 0) . ' sqm Land in ' . $validated['municipality'];
            if ($validated['barangay']) {
                $propertyTitle .= ', ' . $validated['barangay'];
            }

            // Create seller request - mapping client fields to public form structure
            $sellerRequest = SellerRequest::create([
                'client_id' => $client->id,
                'name' => $validated['contact_name'],
                'email' => $validated['contact_email'],
                'phone' => $validated['contact_phone'],
                'property_type' => $validated['property_type'] ?? 'residential_lot', // Default to residential_lot
                'property_title' => $propertyTitle,
                'property_description' => $validated['description'] ?? 'Land for sale',
                'address' => $validated['address'],
                'city' => $validated['municipality'],
                'province' => 'Bohol',
                'municipality' => $validated['municipality'],
                'barangay' => $validated['barangay'],
                'lot_area' => $validated['lot_area'],
                'asking_price' => $validated['price_expectation'],
                'price_expectation' => $validated['price_expectation'],
                'description' => $validated['description'],
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'status' => 'pending',
                'submission_date' => now(),
            ]);

            // Handle image uploads - save to both fields for compatibility
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('seller-requests/images', 'public');
                    $images[] = $path;
                }
                $sellerRequest->update([
                    'images' => json_encode($images),
                    'uploaded_images' => json_encode($images), // For admin view compatibility
                ]);
            }

            // Handle document uploads - save to both fields for compatibility
            if ($request->hasFile('documents')) {
                $documents = [];
                foreach ($request->file('documents') as $document) {
                    $path = $document->store('seller-requests/documents', 'public');
                    $documents[] = [
                        'path' => $path,
                        'name' => $document->getClientOriginalName(),
                    ];
                }
                $sellerRequest->update([
                    'documents' => json_encode($documents),
                    'property_documents' => json_encode($documents), // For admin view compatibility
                ]);
            }

            DB::commit();

            Log::info('Seller request created by client', [
                'seller_request_id' => $sellerRequest->id,
                'client_id' => $client->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('client.seller-requests.index')
                ->with('success', 'Your property listing request has been submitted successfully! A broker will be assigned to you shortly.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create seller request', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to submit your request. Please try again.');
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
