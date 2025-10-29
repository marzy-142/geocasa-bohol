<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\TransactionStatusNotification;
use App\Events\TransactionCreated;
use App\Events\TransactionStatusUpdated;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Optimized eager loading with specific columns
        $query = Transaction::with([
            'property:id,title,slug,address,municipality,total_price,broker_id',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type'
        ]);
        
        // Role-based filtering
        if ($user->role === 'broker') {
            $query->where('broker_id', $user->id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('inquiry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('inquiry_date', '<=', $request->date_to);
        }
        
        // Property filter
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['created_at', 'inquiry_date', 'offered_price', 'status', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        $transactions = $query->paginate(12);
        
        // Optimized filter options - only get what's needed
        $properties = Property::select('id', 'title')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->where('broker_id', $user->id);
            })
            ->orderBy('title')
            ->get();
        $statuses = [
            'inquiry' => 'Inquiry',
            'initial_contact' => 'Initial Contact',
            'property_viewing' => 'Property Viewing',
            'offer_made' => 'Offer Made',
            'negotiation' => 'Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Finalized',
            'cancelled' => 'Cancelled'
        ];
        
        // Basic stats for the dashboard
        $stats = [
            'total_transactions' => $query->count(),
            'active_transactions' => $query->whereNotIn('status', ['finalized', 'cancelled'])->count(),
            'total_value' => $query->sum('offered_price'),
        ];

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'property_id', 'sort_by', 'sort_order']),
            'properties' => $properties,
            'statuses' => $statuses,
            'canCreate' => $user->role === 'broker' || $user->role === 'admin',
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Check if creating from a specific inquiry
        $selectedInquiry = null;
        if ($request->has('inquiry_id')) {
            $selectedInquiry = Inquiry::with(['property', 'client'])
                ->where('id', $request->inquiry_id)
                ->whereHas('property', function($q) use ($user) {
                    if ($user->role === 'broker') {
                        $q->where('broker_id', $user->id);
                    }
                })
                ->first();
        }
        
        // Optimized data loading for form
        $properties = Property::select('id', 'title', 'address', 'municipality', 'total_price', 'type', 'status')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->where('broker_id', $user->id);
            })
            ->orderBy('title')
            ->get()
            ->map(function($property) {
                // Add computed fields for the frontend
                $property->price = $property->total_price;
                // type is already the correct column name, no need to map
                $property->location = $property->address . ', ' . $property->municipality;
                return $property;
            });
            
        $clients = Client::select('id', 'name', 'email', 'phone')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->where('broker_id', $user->id);
            })
            ->orderBy('name')
            ->get();
            
        $inquiries = Inquiry::with(['property:id,title,address,municipality,total_price', 'client:id,name,email,phone'])
            ->select('id', 'property_id', 'client_id', 'inquiry_type', 'status', 'message', 'created_at')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->whereHas('property', function($subQuery) use ($user) {
                    $subQuery->where('broker_id', $user->id);
                });
            })
            ->whereIn('status', ['new', 'contacted', 'scheduled'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Transactions/Create', [
            'properties' => $properties,
            'clients' => $clients,
            'inquiries' => $inquiries,
            'selectedInquiry' => $selectedInquiry,
        ]);
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'offered_price' => 'required|numeric|min:0',
            'inquiry_date' => 'required|date',
            'broker_notes' => 'nullable|string',
        ]);
        
        $validated['broker_id'] = $user->id;
        $validated['status'] = 'inquiry';
        
        $transaction = Transaction::create($validated);
        
        // Fire the TransactionCreated event for real-time updates
        event(new TransactionCreated($transaction));
        
        // Update related inquiry status if provided
        if ($validated['inquiry_id']) {
            Inquiry::find($validated['inquiry_id'])->update(['status' => 'in transaction']);
        }
        
        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->load([
            'property:id,title,slug,address,municipality,total_price,type,status,broker_id',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type,status'
        ]);
        
        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->load([
            'property:id,title,address,municipality,total_price,type,status,broker_id',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type,status'
        ]);
        
        // Optimized data loading for form
        $properties = Property::select('id', 'title', 'address', 'municipality', 'total_price', 'type', 'status')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->where('broker_id', $user->id);
            })
            ->orderBy('title')
            ->get();
            
        $clients = Client::select('id', 'name', 'email', 'phone')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->where('broker_id', $user->id);
            })
            ->orderBy('name')
            ->get();
            
        $brokers = User::select('id', 'name', 'email')
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
            
        $inquiries = Inquiry::select('id', 'property_id', 'client_id', 'inquiry_type', 'status', 'message', 'created_at')
            ->when($user->role === 'broker', function($q) use ($user) {
                return $q->whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                });
            })
            ->with(['property:id,title,address', 'client:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $statuses = [
            'inquiry' => 'Inquiry',
            'initial_contact' => 'Initial Contact',
            'property_viewing' => 'Property Viewing',
            'offer_made' => 'Offer Made',
            'negotiation' => 'Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Finalized',
            'cancelled' => 'Cancelled'
        ];
        
        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'properties' => $properties,
            'clients' => $clients,
            'brokers' => $brokers,
            'inquiries' => $inquiries,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update the specified transaction with enhanced validation and audit logging
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Enhanced authorization check
        $this->validateTransactionAccess($transaction, $user, 'update');
        
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'offered_price' => 'required|numeric|min:0',
            'final_price' => 'nullable|numeric|min:0',
            'inquiry_date' => 'required|date',
            'first_contact_date' => 'nullable|date',
            'viewing_date' => 'nullable|date',
            'offer_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'closing_date' => 'nullable|date',
            'finalized_date' => 'nullable|date',
            'broker_notes' => 'nullable|string',
            'reason' => 'nullable|string|max:500', // Reason for changes
        ]);
        
        // Handle status updates separately with state machine validation
        $newStatus = $request->input('status');
        $statusUpdateResult = null;
        
        if ($newStatus && $newStatus !== $transaction->status) {
            $stateMachine = app(\App\Services\TransactionStateMachine::class);
            $statusUpdateResult = $stateMachine->transition(
                $transaction, 
                $newStatus, 
                $user, 
                $validated['reason'] ?? 'Status updated by ' . $user->role
            );
            
            if (!$statusUpdateResult['success']) {
                return back()->withErrors([
                    'status' => $statusUpdateResult['error']
                ])->withInput();
            }
        }
        
        // Auto-set finalized_date if status is finalized
        if ($newStatus === 'finalized' && !$validated['finalized_date']) {
            $validated['finalized_date'] = now();
        }
        
        // Log field changes for audit
        $auditService = app(\App\Services\TransactionAuditService::class);
        $oldValues = $transaction->only(['offered_price', 'final_price', 'broker_notes']);
        
        $transaction->update($validated);
        
        // Log field updates
        foreach ($oldValues as $field => $oldValue) {
            if ($oldValue != $transaction->$field) {
                $auditService->logFieldUpdate(
                    $transaction,
                    $field,
                    $oldValue,
                    $transaction->$field,
                    $user,
                    $validated['reason'] ?? 'Field updated by ' . $user->role
                );
            }
        }
        
        // Fire the TransactionStatusUpdated event for real-time updates
        if ($statusUpdateResult && $statusUpdateResult['success']) {
            event(new TransactionStatusUpdated($transaction, $statusUpdateResult['old_status'], $statusUpdateResult['new_status']));
        }
        
        // Send notification if status changed
        if ($statusUpdateResult && $statusUpdateResult['success']) {
            $transaction->broker->notify(new TransactionStatusNotification($transaction, $statusUpdateResult['old_status']));
        }

        // Trigger completion workflow if transaction is finalized
        if ($validated['status'] === 'finalized') {
            $completionService = app(\App\Services\TransactionCompletionService::class);
            $completionResult = $completionService->completeTransaction($transaction);
            
            if ($completionResult['success']) {
                return redirect()->route('transactions.show', $transaction)
                    ->with('success', 'Transaction completed successfully! All parties have been notified and the property has been marked as sold.');
            } else {
                return redirect()->route('transactions.show', $transaction)
                    ->with('warning', 'Transaction finalized but completion workflow failed: ' . $completionResult['message']);
            }
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->delete();
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:inquiry,initial_contact,property_viewing,offer_made,negotiation,offer_accepted,contract_signed,due_diligence,financing,closing_preparation,finalized,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $updateData = ['status' => $validated['status']];
        
        // Auto-set relevant dates based on status
        switch ($validated['status']) {
            case 'initial_contact':
                if (!$transaction->first_contact_date) {
                    $updateData['first_contact_date'] = now();
                }
                break;
            case 'property_viewing':
                if (!$transaction->viewing_date) {
                    $updateData['viewing_date'] = now();
                }
                break;
            case 'offer_made':
                if (!$transaction->offer_date) {
                    $updateData['offer_date'] = now();
                }
                break;
            case 'offer_accepted':
                if (!$transaction->acceptance_date) {
                    $updateData['acceptance_date'] = now();
                }
                break;
            case 'contract_signed':
                if (!$transaction->contract_date) {
                    $updateData['contract_date'] = now();
                }
                break;
            case 'finalized':
                if (!$transaction->finalized_date) {
                    $updateData['finalized_date'] = now();
                }
                break;
        }
        
        if ($validated['notes']) {
            $updateData['broker_notes'] = $transaction->broker_notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $validated['notes'];
        }
        
        $transaction->update($updateData);

        // Synchronize inquiry status if linked
        if ($transaction->inquiry) {
            $inquiryStatus = null;
            switch ($validated['status']) {
                case 'inquiry':
                case 'initial_contact':
                    $inquiryStatus = 'in transaction';
                    break;
                case 'property_viewing':
                case 'offer_made':
                case 'negotiation':
                case 'offer_accepted':
                case 'contract_signed':
                case 'due_diligence':
                case 'financing':
                case 'closing_preparation':
                    $inquiryStatus = 'in transaction';
                    break;
                case 'finalized':
                    $inquiryStatus = 'closed';
                    break;
                case 'cancelled':
                    $inquiryStatus = 'not converted';
                    break;
            }
            if ($inquiryStatus) {
                $transaction->inquiry->update(['status' => $inquiryStatus]);
            }
        }

        // Fire the TransactionStatusUpdated event for real-time updates
        event(new TransactionStatusUpdated($transaction, $transaction->getOriginal('status'), $validated['status']));

        // Trigger completion workflow if transaction is finalized
        if ($validated['status'] === 'finalized') {
            $completionService = app(\App\Services\TransactionCompletionService::class);
            $completionResult = $completionService->completeTransaction($transaction);
            
            if ($completionResult['success']) {
                return back()->with('success', 'Transaction completed successfully! All parties have been notified and the property has been marked as sold.');
            } else {
                return back()->with('warning', 'Transaction finalized but completion workflow failed: ' . $completionResult['message']);
            }
        }

        return back()->with('success', 'Transaction status updated successfully.');
    }

    /**
     * Admin: Display a listing of all transactions
     */
    public function adminIndex(Request $request)
    {
        // Admin can see all transactions
        $query = Transaction::with([
            'property:id,title,slug,address,municipality,total_price,broker_id',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type'
        ]);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('broker', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Broker filter
        if ($request->filled('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('inquiry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('inquiry_date', '<=', $request->date_to);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Filter options for admin
        $brokers = User::select('id', 'name')
            ->where('role', 'broker')
            ->where('application_status', 'approved')
            ->orderBy('name')
            ->get();
            
        $statuses = [
            'inquiry' => 'Inquiry',
            'initial_contact' => 'Initial Contact',
            'property_viewing' => 'Property Viewing',
            'offer_made' => 'Offer Made',
            'negotiation' => 'Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Finalized',
            'cancelled' => 'Cancelled'
        ];
        
        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status', 'broker_id', 'date_from', 'date_to']),
            'brokers' => $brokers,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Admin: Display the specified transaction
     */
    public function adminShow(Transaction $transaction)
    {
        $transaction->load([
            'property:id,title,slug,address,municipality,total_price,type,status,broker_id',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type,status'
        ]);
        
        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Admin: Update transaction status
     */
    public function adminUpdateStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => 'required|in:inquiry,initial_contact,property_viewing,offer_made,negotiation,offer_accepted,contract_signed,due_diligence,financing,closing_preparation,finalized,cancelled',
            'admin_notes' => 'nullable|string',
        ]);
        
        $updateData = ['status' => $validated['status']];
        
        // Auto-set relevant dates based on status
        switch ($validated['status']) {
            case 'initial_contact':
                if (!$transaction->first_contact_date) {
                    $updateData['first_contact_date'] = now();
                }
                break;
            case 'property_viewing':
                if (!$transaction->viewing_date) {
                    $updateData['viewing_date'] = now();
                }
                break;
            case 'offer_made':
                if (!$transaction->offer_date) {
                    $updateData['offer_date'] = now();
                }
                break;
            case 'offer_accepted':
                if (!$transaction->acceptance_date) {
                    $updateData['acceptance_date'] = now();
                }
                break;
            case 'contract_signed':
                if (!$transaction->contract_date) {
                    $updateData['contract_date'] = now();
                }
                break;
            case 'finalized':
                if (!$transaction->finalized_date) {
                    $updateData['finalized_date'] = now();
                }
                break;
        }
        
        if ($validated['admin_notes']) {
            $updateData['broker_notes'] = $transaction->broker_notes . "\n\n" . now()->format('Y-m-d H:i') . " - [ADMIN] " . $validated['admin_notes'];
        }
        
        $transaction->update($updateData);
        
        // Send notification to broker about admin status change
        $transaction->broker->notify(new TransactionStatusNotification($transaction, $transaction->getOriginal('status')));
        
        return back()->with('success', 'Transaction status updated successfully by admin.');
    }
    
    /**
     * Admin: Get comprehensive transaction statistics
     */
    public function adminStatistics(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30));
        $dateTo = $request->get('date_to', now());

        $query = Transaction::whereBetween('created_at', [$dateFrom, $dateTo]);

        // System-wide statistics
        $totalTransactions = $query->count();
        $activeTransactions = $query->whereNotIn('status', ['finalized', 'cancelled'])->count();
        $finalizedTransactions = $query->where('status', 'finalized')->count();
        $cancelledTransactions = $query->where('status', 'cancelled')->count();

        // Financial statistics
        $totalValue = $query->where('status', 'finalized')->sum(DB::raw('COALESCE(final_price, offered_price)'));
        $averageDealValue = $finalizedTransactions > 0 ? $totalValue / $finalizedTransactions : 0;

        // Performance metrics
        $successRate = $totalTransactions > 0 ? round(($finalizedTransactions / $totalTransactions) * 100, 2) : 0;
        
        // Calculate average deal time
        $avgDealTime = Transaction::where('status', 'finalized')
            ->whereNotNull('finalized_date')
            ->whereNotNull('inquiry_date')
            ->selectRaw('AVG(DATEDIFF(finalized_date, inquiry_date)) as avg_days')
            ->value('avg_days') ?? 0;

        // Broker performance
        $brokerStats = User::where('role', 'broker')
            ->where('application_status', 'approved')
            ->withCount([
                'transactions as total_transactions' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'transactions as finalized_transactions' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'finalized')->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            ])
            ->get()
            ->map(function ($broker) {
                $broker->success_rate = $broker->total_transactions > 0 
                    ? round(($broker->finalized_transactions / $broker->total_transactions) * 100, 2) 
                    : 0;
                return $broker;
            });

        // Status distribution
        $statusDistribution = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly trends
        $monthlyTrends = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(CASE WHEN status = "finalized" THEN final_price ELSE 0 END) as value')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'system_stats' => [
                'total_transactions' => $totalTransactions,
                'active_transactions' => $activeTransactions,
                'finalized_transactions' => $finalizedTransactions,
                'cancelled_transactions' => $cancelledTransactions,
            ],
            'financial_stats' => [
                'total_value' => $totalValue,
                'average_deal_value' => round($averageDealValue, 2),
            ],
            'performance_metrics' => [
                'success_rate' => $successRate,
                'average_deal_time_days' => round($avgDealTime, 1),
            ],
            'broker_performance' => $brokerStats,
            'status_distribution' => $statusDistribution,
            'monthly_trends' => $monthlyTrends,
        ]);
    }

    /**
     * Admin: Export transactions data
     */
    public function adminExport(Request $request)
    {
        $query = Transaction::with([
            'property:id,title,slug,address,municipality,total_price',
            'client:id,name,email,phone',
            'broker:id,name,email',
            'inquiry:id,property_id,client_id,inquiry_type'
        ]);

        // Apply same filters as adminIndex
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('broker', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('inquiry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('inquiry_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'transactions_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Transaction ID',
                'Transaction Number',
                'Status',
                'Property Title',
                'Property Location',
                'Property Price',
                'Client Name',
                'Client Email',
                'Client Phone',
                'Broker Name',
                'Broker Email',
                'Offered Price',
                'Final Price',
                'Sales Value',
                'Inquiry Date',
                'First Contact Date',
                'Viewing Date',
                'Offer Date',
                'Acceptance Date',
                'Contract Date',
                'Finalized Date',
                'Created At',
                'Updated At'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->transaction_number,
                    $transaction->status,
                    $transaction->property->title ?? 'N/A',
                    $transaction->property->address . ', ' . $transaction->property->municipality ?? 'N/A',
                    $transaction->property->total_price ?? 'N/A',
                    $transaction->client->name ?? 'N/A',
                    $transaction->client->email ?? 'N/A',
                    $transaction->client->phone ?? 'N/A',
                    $transaction->broker->name ?? 'N/A',
                    $transaction->broker->email ?? 'N/A',
                    $transaction->offered_price,
                    $transaction->final_price ?? 'N/A',
                    $transaction->final_price ?? $transaction->offered_price,
                    $transaction->inquiry_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->first_contact_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->viewing_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->offer_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->acceptance_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->contract_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->finalized_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Admin: Bulk update transaction statuses
     */
    public function adminBulkUpdate(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:transactions,id',
            'action' => 'required|in:update_status,assign_broker,add_notes',
            'status' => 'required_if:action,update_status|in:inquiry,initial_contact,property_viewing,offer_made,negotiation,offer_accepted,contract_signed,due_diligence,financing,closing_preparation,finalized,cancelled',
            'broker_id' => 'required_if:action,assign_broker|exists:users,id',
            'notes' => 'required_if:action,add_notes|string|max:1000',
        ]);

        $transactions = Transaction::whereIn('id', $request->transaction_ids)->get();
        $updated = 0;

        foreach ($transactions as $transaction) {
            switch ($request->action) {
                case 'update_status':
                    $transaction->update(['status' => $request->status]);
                    $updated++;
                    break;
                case 'assign_broker':
                    $transaction->update(['broker_id' => $request->broker_id]);
                    $updated++;
                    break;
                case 'add_notes':
                    $newNotes = $transaction->broker_notes . "\n\n" . now()->format('Y-m-d H:i') . " - [ADMIN BULK] " . $request->notes;
                    $transaction->update(['broker_notes' => $newNotes]);
                    $updated++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updated} transactions.",
            'updated_count' => $updated
        ]);
    }

    /**
     * Validate transaction access based on user role and permissions
     */
    private function validateTransactionAccess(Transaction $transaction, User $user, string $action): void
    {
        // Admin can do anything
        if ($user->role === 'admin') {
            return;
        }

        // Broker can only access their own transactions
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        // Client can only view their own transactions
        if ($user->role === 'client') {
            $client = $user->client;
            if (!$client || $transaction->client_id !== $client->id) {
                abort(403, 'Unauthorized access to transaction.');
            }
            
            // Clients can only view, not update
            if ($action !== 'view') {
                abort(403, 'Clients can only view transactions.');
            }
        }

        // Check if user is approved (for brokers)
        if ($user->role === 'broker' && !$user->is_approved) {
            abort(403, 'Broker account is not approved.');
        }
    }
}