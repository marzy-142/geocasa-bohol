<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InquiryController extends Controller
{
    /**
     * Display a listing of the client's inquiries
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get or create client record for the authenticated user
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
            // Link existing client record to user
            $client->update(['user_id' => $user->id]);
        }
        
        // Build query to get inquiries for this client
        $query = Inquiry::with(['property', 'property.user', 'client'])
            ->where(function ($q) use ($user, $client) {
                $q->where('user_id', $user->id)
                  ->orWhere('client_id', $client->id);
            });
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($propertyQuery) use ($search) {
                      $propertyQuery->where('title', 'like', "%{$search}%")
                                   ->orWhere('location', 'like', "%{$search}%");
                  });
            });
        }
        
        $inquiries = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get assigned broker information
        $broker = $client->broker ? $client->broker->only(['id', 'name', 'email', 'phone']) : null;
        
        return Inertia::render('Client/Inquiries/Index', [
            'inquiries' => $inquiries,
            'filters' => $request->only(['status', 'search']),
            'client' => $client,
            'broker' => $broker,
        ]);
    }
    
    /**
     * Display the specified inquiry
     */
    public function show(Request $request, Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Get client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
        
        // Check if user can access this inquiry
        if (!$client || ($inquiry->user_id !== $user->id && $inquiry->client_id !== $client->id)) {
            abort(403, 'You do not have permission to view this inquiry.');
        }
        
        $inquiry->load(['property', 'property.user', 'client', 'conversation']);
        
        return Inertia::render('Client/Inquiries/Show', [
            'inquiry' => $inquiry,
            'client' => $client
        ]);
    }
    
    /**
     * Update the specified inquiry (for client responses)
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Get client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
        
        // Check if user can update this inquiry
        if (!$client || ($inquiry->user_id !== $user->id && $inquiry->client_id !== $client->id)) {
            abort(403, 'You do not have permission to update this inquiry.');
        }
        
        $validated = $request->validate([
            'client_response' => 'nullable|string|max:1000',
            'budget_range' => 'nullable|string|max:50'
        ]);
        
        $inquiry->update($validated);
        
        return redirect()->back()->with('success', 'Inquiry updated successfully.');
    }
    /**
     * Show the form for creating a new inquiry
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
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

        // Get available properties for the inquiry form
        $properties = \App\Models\Property::where('status', 'available')
            ->select('id', 'title', 'type', 'municipality', 'total_price')
            ->get();

        // Get selected property if property_id is provided
        $selectedProperty = null;
        if ($request->has('property_id')) {
            $selectedProperty = \App\Models\Property::with(['broker:id,name,email,phone'])
                ->find($request->property_id);
        }

        return Inertia::render('Client/Inquiries/Create', [
            'client' => $client,
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
        ]);
    }

    /**
     * Store a newly created inquiry
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'message' => 'required|string|max:1000',
            'inquiry_type' => 'nullable|string|in:general,viewing,price,availability',
            'budget_range' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
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

        // Create the inquiry
        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'property_id' => $request->property_id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone ?? '',
            'message' => $request->message,
            'inquiry_type' => $request->inquiry_type ?? 'general',
            'budget_range' => $request->budget_range,
            'status' => 'new',
        ]);

        // If client has an assigned broker, assign the inquiry to them
        if ($client->broker_id) {
            $inquiry->update(['assigned_broker_id' => $client->broker_id]);
        }

        // Broadcast real-time event so broker views update immediately
        // Ensure property relation is loaded for event payload
        $inquiry->load('property');
        broadcast(new \App\Events\NewInquiryReceived($inquiry));

        return redirect()
            ->route('client.inquiries.show', $inquiry)
            ->with('success', 'Thanks! Your inquiry has been submitted. We sent you a confirmation email and your assigned broker will respond within 24–48 hours. You can track updates anytime in My Inquiries.');
    }
}