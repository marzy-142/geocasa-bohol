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
        
        return Inertia::render('Client/Inquiries/Index', [
            'inquiries' => $inquiries,
            'filters' => $request->only(['status', 'search']),
            'client' => $client
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
        
        $inquiry->load(['property', 'property.user', 'client', 'conversations']);
        
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
            'preferred_contact_method' => 'nullable|in:email,phone,both',
            'budget_range' => 'nullable|string|max:50'
        ]);
        
        $inquiry->update($validated);
        
        return redirect()->back()->with('success', 'Inquiry updated successfully.');
    }
}