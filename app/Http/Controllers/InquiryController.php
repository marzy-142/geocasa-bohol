<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InquiryController extends Controller
{
    /**
     * Display a listing of inquiries
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on user role
        $query = Inquiry::with(['property', 'client']);
        
        // Role-based filtering
        if ($user->role === 'broker') {
            $query->forBroker($user->id);
        }
        
        // Apply filters
        $query->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  });
            });
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($request->inquiry_type, function ($query, $type) {
            $query->where('inquiry_type', $type);
        })
        ->when($request->property_id, function ($query, $propertyId) {
            $query->where('property_id', $propertyId);
        })
        ->when($request->date_from, function ($query, $dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        })
        ->when($request->date_to, function ($query, $dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        });
        
        $inquiries = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get properties for filter (broker sees only their properties)
        $properties = $user->role === 'admin' 
            ? Property::select('id', 'title', 'municipality')->get()
            : Property::where('broker_id', $user->id)->select('id', 'title', 'municipality')->get();
        
        return Inertia::render('Inquiries/Index', [
            'inquiries' => $inquiries,
            'properties' => $properties,
            'filters' => $request->only(['search', 'status', 'inquiry_type', 'property_id', 'date_from', 'date_to']),
            'can' => [
                'create' => false, // Inquiries are created by clients through public interface
                'respond' => $user->role === 'admin' || $user->role === 'broker',
                'delete' => $user->role === 'admin',
            ]
        ]);
    }

    /**
     * Show the form for creating a new inquiry
     * Note: This is disabled - inquiries should be created by clients through public interface
     */
    public function create()
    {
        // Inquiries should only be created by clients through the public interface
        abort(403, 'Inquiries are created by clients through the public property pages.');
    }

    /**
     * Store a newly created inquiry
     * Note: This is disabled - inquiries should be created by clients through public interface
     */
    public function store(Request $request)
    {
        // Inquiries should only be created by clients through the public interface
        abort(403, 'Inquiries are created by clients through the public property pages.');
    }

    /**
     * Display the specified inquiry
     */
    public function show(Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'broker') {
            if ($inquiry->property->broker_id !== $user->id) {
                abort(403);
            }
        }
        
        $inquiry->load(['property.broker', 'client', 'transaction']);
        
        return Inertia::render('Inquiries/Show', [
            'inquiry' => $inquiry,
            'can' => [
                'respond' => $user->role === 'admin' || $user->role === 'broker',
                'edit' => $user->role === 'admin' || ($user->role === 'broker' && $inquiry->property->broker_id === $user->id),
                'delete' => $user->role === 'admin',
            ]
        ]);
    }

    /**
     * Show the form for editing the specified inquiry
     */
    public function edit(Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'broker') {
            if ($inquiry->property->broker_id !== $user->id) {
                abort(403);
            }
        }
        
        $inquiry->load(['property', 'client']);
        
        // Get properties for selection
        $properties = $user->role === 'admin' 
            ? Property::with('broker')->get()
            : Property::where('broker_id', $user->id)->get();
            
        // Get clients for selection
        $clients = $user->role === 'admin'
            ? Client::all()
            : Client::where('broker_id', $user->id)->get();
        
        return Inertia::render('Inquiries/Edit', [
            'inquiry' => $inquiry,
            'properties' => $properties,
            'clients' => $clients,
        ]);
    }

    /**
     * Update the specified inquiry
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'broker') {
            if ($inquiry->property->broker_id !== $user->id) {
                abort(403);
            }
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
            'inquiry_type' => 'required|in:general,viewing,purchase,information',
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'nullable|exists:clients,id',
            'status' => 'required|in:new,contacted,scheduled,completed,closed',
            'broker_notes' => 'nullable|string',
            'broker_response' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);
        
        // Verify property access for brokers
        if ($user->role === 'broker') {
            $property = Property::findOrFail($validated['property_id']);
            if ($property->broker_id !== $user->id) {
                abort(403);
            }
        }
        
        // Set timestamps based on status changes
        if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
            $validated['contacted_at'] = now();
        }
        
        if ($validated['broker_response'] && !$inquiry->responded_at) {
            $validated['responded_at'] = now();
        }
        
        $inquiry->update($validated);
        
        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry updated successfully.');
    }

    /**
     * Remove the specified inquiry
     */
    public function destroy(Inquiry $inquiry)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $inquiry->delete();
        
        return redirect()->route('inquiries.index')
            ->with('success', 'Inquiry deleted successfully.');
    }

    /**
     * Respond to an inquiry
     */
    public function respond(Request $request, Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'broker') {
            if ($inquiry->property->broker_id !== $user->id) {
                abort(403);
            }
        }
        
        if (!in_array($user->role, ['admin', 'broker'])) {
            abort(403);
        }
        
        $validated = $request->validate([
            'broker_response' => 'required|string',
            'status' => 'required|in:new,contacted,scheduled,completed,closed',
            'scheduled_at' => 'nullable|date',
        ]);
        
        $validated['responded_at'] = now();
        
        if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
            $validated['contacted_at'] = now();
        }
        
        $inquiry->update($validated);
        
        return back()->with('success', 'Response sent successfully.');
    }
}