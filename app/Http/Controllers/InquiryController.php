<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Events\InquiryStatusUpdated;
use App\Events\NewInquiryReceived;
use App\Mail\InquiryResponseMail;

class InquiryController extends Controller
{
    /**
     * Display a listing of inquiries
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on user role with optimized eager loading
        $query = Inquiry::with([
            'property:id,title,address,municipality,total_price,broker_id',
            'client:id,name,email,phone'
        ]);
        
        // Role-based filtering
        if ($user->role === 'broker') {
            $query->forBroker($user->id);
        } elseif ($user->role === 'client') {
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
            
            $query->where(function ($q) use ($user, $client) {
                $q->where('user_id', $user->id)
                  ->orWhere('client_id', $client->id);
            });
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
            if ($inquiry->assigned_broker_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->role === 'client') {
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
            
            // Check if client can access this inquiry
            if ($inquiry->user_id !== $user->id && $inquiry->client_id !== $client->id) {
                abort(403);
            }
        }
        
        $inquiry->load([
            'property:id,title,slug,address,municipality,total_price,broker_id',
            'property.broker:id,name,email',
            'client:id,name,email,phone',
            'transaction:id,status,transaction_number'
        ]);
        
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
            if ($inquiry->assigned_broker_id !== $user->id) {
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
        // Store previous status for event
        $previousStatus = $inquiry->status;
        
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'broker') {
            if ($inquiry->assigned_broker_id !== $user->id) {
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
        
        // Verify property access for brokers - this check can be removed since we now use assigned_broker_id
        // The broker assigned to handle the inquiry may be different from the property owner
        // if ($user->role === 'broker') {
        //     $property = Property::findOrFail($validated['property_id']);
        //     if ($property->broker_id !== $user->id) {
        //         abort(403);
        //     }
        // }
        
        // Set timestamps based on status changes
        if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
            $validated['contacted_at'] = now();
        }
        
        if ($validated['broker_response'] && !$inquiry->responded_at) {
            $validated['responded_at'] = now();
        }
        
        $inquiry->update($validated);
        
        // Broadcast status change if status was updated
        if ($previousStatus !== $inquiry->status) {
            broadcast(new InquiryStatusUpdated(
                $inquiry->fresh(['property']), 
                $previousStatus, 
                $inquiry->status,
                Auth::user()->name
            ));
        }
        
        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry updated successfully.');
    }

    /**
     * Accept inquiry and convert to transaction
     */
    public function accept(Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Only brokers can accept inquiries
        if ($user->role !== 'broker' && $user->role !== 'admin') {
            abort(403, 'Only brokers can accept inquiries');
        }
        
        // Check if broker is assigned to this inquiry
        if ($user->role === 'broker' && $inquiry->assigned_broker_id !== $user->id) {
            abort(403, 'You are not assigned to this inquiry');
        }
        
        // Check if inquiry already has a transaction
        if ($inquiry->transaction) {
            // Redirect to the existing transaction view instead of erroring out
            return redirect()
                ->route('transactions.show', $inquiry->transaction->id)
                ->with('info', 'This inquiry already has a transaction.');
        }
        
        $previousStatus = $inquiry->status;
        
        DB::transaction(function () use (&$inquiry, $user, $previousStatus) {
            // 1. Auto-normalize inquiry status/timestamps
            $update = [ 'status' => 'in transaction' ];
            if (is_null($inquiry->responded_at)) {
                $update['responded_at'] = now();
            }
            if ($previousStatus === 'new' && is_null($inquiry->contacted_at)) {
                $update['contacted_at'] = now();
            }
            $inquiry->update($update);
            
            // 2. Create transaction
            $transaction = Transaction::create([
                'inquiry_id' => $inquiry->id,
                'property_id' => $inquiry->property_id,
                'client_id' => $inquiry->client_id,
                'broker_id' => $inquiry->assigned_broker_id ?? $user->id,
                'status' => 'initial_contact',
                'transaction_number' => 'TXN-' . strtoupper(Str::random(10)),
                'offered_price' => $inquiry->property->total_price ?? 0,
                'inquiry_date' => $inquiry->created_at,
                'client_engagement_score' => 50, // Default starting score
            ]);
            
            // 3. Find or create conversation for this inquiry
            $conversation = $inquiry->conversation;
            
            if (!$conversation) {
                // Create conversation if it doesn't exist
                $conversation = Conversation::createForInquiry($inquiry);
            }
            
            // 4. Transition the conversation to transaction
            $conversation->transitionToTransaction($transaction);
        });
        
        // 5. Broadcast status change so client/broker UIs update immediately (after DB commit)
        if ($previousStatus !== 'in transaction') {
            broadcast(new InquiryStatusUpdated(
                $inquiry->fresh(['property']),
                $previousStatus,
                'in transaction',
                Auth::user()->name
            ));
        }
        
        return redirect()
            ->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry accepted! Transaction has been created.');
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
        // Store previous status for event
        $previousStatus = $inquiry->status;
        
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
            'completion_outcome' => 'nullable|in:won,lost,no_response,other',
            'completion_reason' => 'nullable|string|max:255',
            'completion_notes' => 'nullable|string|max:2000',
        ]);

        // Timestamps derived from action
        $updateData = [
            'broker_response' => $validated['broker_response'],
            'status' => $validated['status'],
            'responded_at' => now(),
        ];

        if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
            $updateData['contacted_at'] = now();
        }

        if (!empty($validated['scheduled_at'])) {
            $updateData['scheduled_at'] = $validated['scheduled_at'];
        }

        // When marking as completed, require an outcome for better reporting
        if ($validated['status'] === 'completed' && empty($request->completion_outcome)) {
            return redirect()->back()
                ->withErrors(['completion_outcome' => 'Outcome is required when marking an inquiry as completed.'])
                ->withInput();
        }

        // Apply completion fields when provided (for completed or closed)
        if (in_array($validated['status'], ['completed', 'closed'])) {
            if ($request->filled('completion_outcome')) {
                $updateData['completion_outcome'] = $request->input('completion_outcome');
            }
            if ($request->filled('completion_reason')) {
                $updateData['completion_reason'] = $request->input('completion_reason');
            }
            if ($request->filled('completion_notes')) {
                $updateData['completion_notes'] = $request->input('completion_notes');
            }
        }

        $inquiry->update($updateData);

        // Send email notification to the client
        try {
            Mail::to($inquiry->email)->send(
                new InquiryResponseMail(
                    $inquiry->fresh(['property']),
                    $validated['broker_response'],
                    Auth::user()->name
                )
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the response
            \Log::error('Failed to send inquiry response email: ' . $e->getMessage());
        }

        // Broadcast status change with fresh state
        broadcast(new InquiryStatusUpdated(
            $inquiry->fresh(['property']),
            $previousStatus,
            $inquiry->status,
            Auth::user()->name
        ));

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Response sent successfully and client has been notified via email.');
    }

    /**
     * Admin inquiries index with system-wide analytics
     */
    public function adminIndex(Request $request)
    {
        $query = Inquiry::with([
            'property:id,title,address,municipality,total_price',
            'client:id,name,email,phone',
            'broker:id,name,email'
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $inquiries = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get brokers with inquiry counts
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount('assignedInquiries')
            ->get(['id', 'name'])
            ->map(function ($broker) {
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'inquiry_count' => $broker->assigned_inquiries_count ?? 0
                ];
            });

        // Get properties
        $properties = Property::select('id', 'title', 'municipality')->get();

        // Calculate system stats
        $systemStats = [
            'total_inquiries' => Inquiry::count(),
            'pending' => Inquiry::where('status', 'new')->count(),
            'overdue' => Inquiry::where('status', 'new')
                ->where('created_at', '<', now()->subDays(2))
                ->count(),
            'response_rate' => $this->calculateResponseRate(),
        ];

        // Calculate metrics
        $metrics = [
            'avg_response_time' => $this->calculateAvgResponseTime(),
            'response_time_trend' => $this->calculateResponseTimeTrend(),
            'conversion_rate' => $this->calculateConversionRate(),
            'conversion_trend' => $this->calculateConversionTrend(),
            'active_brokers' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->whereHas('assignedInquiries', function($q) {
                    $q->where('created_at', '>', now()->subDays(30));
                })
                ->count(),
            'broker_utilization' => $this->calculateBrokerUtilization(),
            'flagged_issues' => Inquiry::where('is_flagged', true)->count(),
        ];

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $inquiries,
            'properties' => $properties,
            'brokers' => $brokers,
            'filters' => $request->only([
                'search', 'status', 'broker_id', 'property_id', 
                'priority', 'inquiry_type', 'date_from', 'date_to',
                'response_time', 'sort_by'
            ]),
            'systemStats' => $systemStats,
            'metrics' => $metrics,
        ]);
    }

    private function calculateResponseRate()
    {
        $total = Inquiry::count();
        if ($total === 0) return 0;
        
        $responded = Inquiry::whereNotNull('responded_at')->count();
        return round(($responded / $total) * 100, 1);
    }

    private function calculateAvgResponseTime()
    {
        $avg = Inquiry::whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours');

        if (!$avg) return 'N/A';
        if ($avg < 1) return '< 1h';
        if ($avg < 24) return round($avg) . 'h';
        return round($avg / 24, 1) . 'd';
    }

    private function calculateConversionRate()
    {
        $total = Inquiry::count();
        if ($total === 0) return 0;
        
        // Assuming inquiries that lead to transactions are "converted"
        $converted = Inquiry::whereHas('transaction', function($q) {
            $q->where('status', 'finalized');
        })->count();

        return round(($converted / $total) * 100, 1);
    }

    private function calculateResponseTimeTrend()
    {
        $currentPeriod = Inquiry::whereNotNull('responded_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        $previousPeriod = Inquiry::whereNotNull('responded_at')
            ->where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        if ($previousPeriod == 0) return 'Stable';
        
        $change = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
        
        if ($change > 10) return 'Improving';
        if ($change < -10) return 'Declining';
        return 'Stable';
    }

    private function calculateConversionTrend()
    {
        $currentPeriod = Inquiry::where('created_at', '>=', now()->subDays(30))
            ->whereHas('transaction', function($q) {
                $q->where('status', 'finalized');
            })->count();

        $previousPeriod = Inquiry::where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->whereHas('transaction', function($q) {
                $q->where('status', 'finalized');
            })->count();

        if ($previousPeriod == 0) return 'Stable';
        
        $change = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
        
        if ($change > 10) return 'Improving';
        if ($change < -10) return 'Declining';
        return 'Stable';
    }

    private function calculateBrokerUtilization()
    {
        $totalBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->count();

        if ($totalBrokers === 0) return 0;

        $activeBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereHas('assignedInquiries', function($q) {
                $q->where('created_at', '>', now()->subDays(30));
            })
            ->count();

        return round(($activeBrokers / $totalBrokers) * 100, 1);
    }

    /**
     * Reassign inquiry to a different broker
     */
    public function reassign(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'broker_id' => 'required|exists:users,id',
        ]);

        $oldBroker = $inquiry->broker;
        $newBroker = User::findOrFail($request->broker_id);

        $inquiry->update([
            'assigned_broker_id' => $request->broker_id,
        ]);

        return back()->with('success', 'Inquiry reassigned successfully from ' . ($oldBroker?->name ?? 'Unassigned') . ' to ' . $newBroker->name);
    }

    /**
     * Flag or unflag an inquiry
     */
    public function flag(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'is_flagged' => 'required|boolean',
            'flag_reason' => 'nullable|string|max:255'
        ]);

        $inquiry->update([
            'is_flagged' => $request->is_flagged,
            'flag_reason' => $request->flag_reason,
            'flagged_at' => $request->is_flagged ? now() : null,
            'flagged_by' => $request->is_flagged ? Auth::id() : null,
        ]);

        $message = $request->is_flagged 
            ? 'Inquiry flagged successfully.' 
            : 'Flag removed successfully.';

        return back()->with('success', $message);
    }

    /**
     * Export inquiries data
     */
    public function export(Request $request)
    {
        $query = Inquiry::with([
            'property:id,title,address,municipality,total_price',
            'client:id,name,email,phone',
            'broker:id,name,email'
        ]);

        // Apply same filters as adminIndex
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $inquiries = $query->orderBy('created_at', 'desc')->get();

        $filename = 'inquiries_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inquiries) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Message',
                'Inquiry Type',
                'Status',
                'Property Title',
                'Property Location',
                'Property Price',
                'Assigned Broker',
                'Client Name',
                'Created At',
                'Contacted At',
                'Responded At',
                'Is Flagged',
                'Flag Reason'
            ]);

            // CSV data
            foreach ($inquiries as $inquiry) {
                fputcsv($file, [
                    $inquiry->id,
                    $inquiry->name,
                    $inquiry->email,
                    $inquiry->phone,
                    $inquiry->message,
                    $inquiry->inquiry_type,
                    $inquiry->status,
                    $inquiry->property->title ?? 'N/A',
                    $inquiry->property->municipality ?? 'N/A',
                    $inquiry->property->total_price ?? 'N/A',
                    $inquiry->broker->name ?? 'Unassigned',
                    $inquiry->client->name ?? 'N/A',
                    $inquiry->created_at->format('Y-m-d H:i:s'),
                    $inquiry->contacted_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $inquiry->responded_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $inquiry->is_flagged ? 'Yes' : 'No',
                    $inquiry->flag_reason ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}