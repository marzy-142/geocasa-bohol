<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Property;
use App\Models\User;
use App\Notifications\BrokerAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on user role
        $query = Client::with(['broker', 'inquiries', 'transactions']);
        
        if ($user->role === 'broker') {
            $query->where('broker_id', $user->id);
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('broker_id') && $user->role === 'admin') {
            $query->where('broker_id', $request->broker_id);
        }
        
        // Budget range filter
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', $request->budget_min);
        }
        
        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', $request->budget_max);
        }
        
        $clients = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get brokers for filter (admin only)
        $brokers = $user->role === 'admin' 
            ? User::where('role', 'broker')->where('is_approved', true)->get()
            : collect();
        
        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'brokers' => $brokers,
            'filters' => $request->only(['search', 'status', 'source', 'broker_id', 'budget_min', 'budget_max']),
            'statuses' => ['active', 'inactive', 'converted'],
            'sources' => ['website', 'referral', 'social_media', 'walk_in', 'phone_call'],
            'municipalities' => [
                'Tagbilaran City', 'Baclayon', 'Alburquerque', 'Loboc', 'Sevilla',
                'Calape', 'Tubigon', 'Clarin', 'Inabanga', 'Sagbayan', 'Catigbian',
                'Batuan', 'Balilihan', 'Carmen', 'Sierra Bullones', 'Pilar',
                'Dagohoy', 'Danao', 'Trinidad', 'Talibon', 'Bien Unido',
                'Ubay', 'San Miguel', 'Candijay', 'Mabini', 'Guindulman',
                'Duero', 'Anda', 'Jagna', 'Garcia Hernandez', 'Valencia',
                'Dimiao', 'Loon', 'Maribojoc', 'Antequera', 'Cortes',
                'Corella', 'Sikatuna', 'Loay', 'Albur', 'Panglao',
                'Dauis', 'President Carlos P. Garcia', 'Buenavista', 'Getafe',
                'San Isidro', 'Chocolate Hills', 'Bilar'
            ],
            'can' => [
                'create' => $user->role === 'admin' || $user->role === 'broker',
                'edit' => $user->role === 'admin' || $user->role === 'broker',
                'delete' => $user->role === 'admin',
            ]
        ]);
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'broker'])) {
            abort(403);
        }
        
        $brokers = $user->role === 'admin' 
            ? User::where('role', 'broker')->where('is_approved', true)->get()
            : collect([$user]);
        
        return Inertia::render('Clients/Create', [
            'brokers' => $brokers,
            'bohlMunicipalities' => $this->getBohlMunicipalities(),
        ]);
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'broker'])) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'preferred_location' => 'nullable|string|max:255',
            'preferred_area_min' => 'nullable|numeric|min:0',
            'preferred_area_max' => 'nullable|numeric|min:0|gte:preferred_area_min',
            'preferred_features' => 'nullable|array',
            'broker_id' => 'required|exists:users,id',
            'source' => 'required|in:inquiry,manual,referral,website',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,converted',
        ]);
        
        // Ensure broker assignment is valid
        if ($user->role === 'broker' && $validated['broker_id'] != $user->id) {
            $validated['broker_id'] = $user->id;
        }
        
        $client = Client::create($validated);
        
        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->role === 'broker' && $client->broker_id !== $user->id) {
            abort(403);
        }
        
        $client->load([
            'broker',
            'inquiries.property',
            'transactions.property'
        ]);
        
        // Get matching properties based on client preferences
        $matchingProperties = $this->getMatchingProperties($client);
        
        // Get recent activity
        $recentActivity = $this->getClientActivity($client);
        
        return Inertia::render('Clients/Show', [
            'client' => $client,
            'matchingProperties' => $matchingProperties,
            'activities' => $recentActivity,
            'can' => [
                'edit' => $user->role === 'admin' || ($user->role === 'broker' && $client->broker_id === $user->id),
                'delete' => $user->role === 'admin',
            ]
        ]);
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->role === 'broker' && $client->broker_id !== $user->id) {
            abort(403);
        }
        
        $brokers = $user->role === 'admin' 
            ? User::where('role', 'broker')->where('is_approved', true)->get()
            : collect([$user]);
        
        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'brokers' => $brokers,
            'bohlMunicipalities' => $this->getBohlMunicipalities(),
        ]);
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->role === 'broker' && $client->broker_id !== $user->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'preferred_location' => 'nullable|string|max:255',
            'preferred_area_min' => 'nullable|numeric|min:0',
            'preferred_area_max' => 'nullable|numeric|min:0|gte:preferred_area_min',
            'preferred_features' => 'nullable|array',
            'broker_id' => 'required|exists:users,id',
            'source' => 'required|in:inquiry,manual,referral,website',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,converted',
        ]);
        
        // Ensure broker assignment is valid
        if ($user->role === 'broker' && $validated['broker_id'] != $user->id) {
            $validated['broker_id'] = $user->id;
        }
        
        $client->update($validated);
        
        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $client->delete();
        
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Assign or reassign a broker to a client (Admin only)
     */
    public function assignBroker(Request $request, Client $client)
    {
        $user = auth()->user();
        
        // Only admin can assign brokers
        if ($user->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        
        $request->validate([
            'broker_id' => 'nullable|exists:users,id'
        ]);
        
        // If broker_id is provided, verify it's an approved broker
        if ($request->broker_id) {
            $broker = User::where('id', $request->broker_id)
                         ->where('role', 'broker')
                         ->where('is_approved', true)
                         ->first();
            
            if (!$broker) {
                return redirect()->back()->with('error', 'Invalid broker selected.');
            }
        }
        
        $previousBrokerId = $client->broker_id;
        $newBrokerId = $request->broker_id;
        
        $client->broker_id = $newBrokerId;
        $client->save();
        
        // Send notification to the new broker if assigned
        if ($newBrokerId) {
            $newBroker = User::find($newBrokerId);
            $action = $previousBrokerId ? 'reassigned' : 'assigned';
            
            $newBroker->notify(new BrokerAssignmentNotification(
                $client, 
                auth()->user(), 
                $action
            ));
        }
        
        $message = $newBrokerId 
            ? "Client assigned to broker successfully."
            : "Client unassigned from broker successfully.";
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk assign brokers to multiple clients (Admin only)
     */
    public function bulkAssignBroker(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can bulk assign brokers to clients.');
        }

        $validated = $request->validate([
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:clients,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        // Verify the broker exists and has broker role
        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->firstOrFail();

        $updatedCount = Client::whereIn('id', $validated['client_ids'])
            ->update(['broker_id' => $validated['broker_id']]);

        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$updatedCount} clients to {$broker->name}",
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Get unassigned clients (Admin only)
     */
    public function getUnassigned(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view unassigned clients.');
        }

        $query = Client::whereNull('broker_id')
            ->with(['inquiries', 'transactions']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Get matching properties for a client
     */
    private function getMatchingProperties(Client $client)
    {
        $query = Property::available()->with('broker');
        
        // Budget matching
        if ($client->budget_min || $client->budget_max) {
            if ($client->budget_min) {
                $query->where('total_price', '>=', $client->budget_min);
            }
            if ($client->budget_max) {
                $query->where('total_price', '<=', $client->budget_max);
            }
        }
        
        // Area matching
        if ($client->preferred_area_min || $client->preferred_area_max) {
            if ($client->preferred_area_min) {
                $query->where('lot_area_sqm', '>=', $client->preferred_area_min * 10000); // Convert hectares to sqm
            }
            if ($client->preferred_area_max) {
                $query->where('lot_area_sqm', '<=', $client->preferred_area_max * 10000);
            }
        }
        
        // Location matching
        if ($client->preferred_location) {
            $query->where('municipality', 'like', '%' . $client->preferred_location . '%');
        }
        
        return $query->limit(6)->get();
    }

    /**
     * Get client activity timeline
     */
    private function getClientActivity(Client $client)
    {
        $activities = collect();
        
        // Add inquiries
        foreach ($client->inquiries as $inquiry) {
            $activities->push([
                'type' => 'inquiry',
                'title' => 'Property Inquiry',
                'description' => 'Inquired about ' . $inquiry->property->title,
                'date' => $inquiry->created_at,
                'status' => $inquiry->status,
            ]);
        }
        
        // Add transactions
        foreach ($client->transactions as $transaction) {
            $activities->push([
                'type' => 'transaction',
                'title' => 'Transaction',
                'description' => 'Transaction for ' . $transaction->property->title,
                'date' => $transaction->created_at,
                'status' => $transaction->status,
            ]);
        }
        
        return $activities->sortByDesc('date')->take(10)->values();
    }

    /**
     * Get Bohol municipalities
     */
    private function getBohlMunicipalities()
    {
        return [
            'Alburquerque', 'Alicia', 'Anda', 'Antequera', 'Baclayon', 'Balilihan', 'Batuan',
            'Bien Unido', 'Bilar', 'Buenavista', 'Calape', 'Candijay', 'Carmen', 'Catigbian',
            'Clarin', 'Corella', 'Cortes', 'Dagohoy', 'Danao', 'Dauis', 'Dimiao', 'Duero',
            'Garcia Hernandez', 'Getafe', 'Guindulman', 'Inabanga', 'Jagna', 'Jetafe', 'Lila',
            'Loay', 'Loboc', 'Loon', 'Mabini', 'Maribojoc', 'Panglao', 'Pilar', 'President Carlos P. Garcia',
            'Sagbayan', 'San Isidro', 'San Miguel', 'Sevilla', 'Sierra Bullones', 'Sikatuna',
            'Tagbilaran City', 'Talibon', 'Trinidad', 'Tubigon', 'Ubay', 'Valencia'
        ];
    }
}