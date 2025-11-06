<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrokerController extends Controller
{
    /**
     * Display comprehensive broker management page
     */
    public function index(Request $request)
    {
        // Start with all brokers (approved and non-approved for admin view)
        $query = User::where('role', 'broker')
            ->with(['properties', 'clients', 'transactions'])
            ->withCount([
                'properties',
                'clients',
                'transactions as transactions_count' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('prc_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->whereNotNull('suspended_at')
                      ->where(function($q) {
                          $q->whereNull('suspended_until')
                            ->orWhere('suspended_until', '>', now());
                      });
            } elseif ($request->status === 'active') {
                // Active means: approved AND not suspended
                $query->where('application_status', 'approved')
                      ->whereNull('suspended_at');
            } elseif ($request->status === 'inactive') {
                // Inactive means: not approved OR suspended
                $query->where(function($q) {
                    $q->where('application_status', '!=', 'approved')
                      ->orWhereNotNull('suspended_at');
                });
            }
        }

        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->where('prc_verified', true);
            } elseif ($request->verification_status === 'unverified') {
                $query->where(function($q) {
                    $q->where('prc_verified', false)
                      ->orWhereNull('prc_verified');
                });
            }
        }

        if ($request->filled('performance_rating')) {
            // Filter by performance rating if implemented
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = 'desc';
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDirection);
                break;
            case 'properties_count':
                $query->orderBy('properties_count', $sortDirection);
                break;
            case 'transactions_count':
                $query->orderBy('transactions_count', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        $brokers = $query->paginate(15);

        // Calculate stats for approved brokers only
        $stats = [
            'totalBrokers' => User::approvedBrokers()->count(),
            'activeBrokers' => User::approvedBrokers()
                ->whereNull('suspended_at')
                ->count(),
            'suspendedBrokers' => User::approvedBrokers()
                ->whereNotNull('suspended_at')
                ->where(function($query) {
                    $query->whereNull('suspended_until')
                          ->orWhere('suspended_until', '>', now());
                })
                ->count(),
            'totalProperties' => Property::whereIn('broker_id', User::approvedBrokers()->pluck('id'))->count(),
        ];

        return Inertia::render('Admin/Brokers/Index', [
            'brokers' => $brokers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'verification_status', 'performance_rating', 'sort']),
        ]);
    }

    /**
     * Display individual broker profile
     */
    public function show(User $broker)
    {
        $broker->load([
            'properties' => function ($query) {
                $query->latest()->limit(10);
            },
            'clients' => function ($query) {
                $query->latest()->limit(10);
            },
            'transactions' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Simple, accurate performance metrics
        $totalProperties = $broker->properties()->count();
        $activeProperties = $broker->properties()->where('status', 'available')->count();
        $totalClients = $broker->clients()->count();
        $activeClients = $broker->clients()->where('status', 'active')->count();
        $totalTransactions = $broker->transactions()->count();
        $completedTransactions = $broker->transactions()->where('status', 'finalized')->count();

        return Inertia::render('Admin/Brokers/Show', [
            'broker' => $broker,
            'properties' => [
                'total' => $totalProperties,
                'active' => $activeProperties,
                'data' => $broker->properties
            ],
            'clients' => [
                'total' => $totalClients,
                'active' => $activeClients,
                'data' => $broker->clients
            ],
            'transactions' => [
                'total' => $totalTransactions,
                'completed' => $completedTransactions,
                'data' => $broker->transactions
            ],
        ]);
    }

    /**
     * Show the form for editing the specified broker
     */
    public function edit(User $broker)
    {
        return Inertia::render('Admin/Brokers/Edit', [
            'broker' => $broker,
        ]);
    }

    /**
     * Update the specified broker in storage
     */
    public function update(Request $request, User $broker)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $broker->id,
            'prc_id' => 'nullable|string|numeric|max:255',
            'business_permit' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
            // Optional: allow admin to set birthdate (must be at least 18 years ago)
            'birthdate' => ['nullable', 'date', 'before_or_equal:' . \Carbon\Carbon::now()->subYears(18)->format('Y-m-d')],
        ]);

        $broker->update($request->only([
            'name',
            'email',
            'prc_id',
            'business_permit',
            'admin_notes',
            'birthdate',
        ]));

        return redirect()->route('admin.brokers.show', $broker)
            ->with('success', 'Broker updated successfully.');
    }

    /**
     * Update broker status
     */
    public function updateStatus(Request $request, User $broker)
    {
        $request->validate([
            'status' => 'required|in:approve,suspend,activate,deactivate',
            'admin_notes' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:500',
        ]);

        switch ($request->status) {
            case 'approve':
                $broker->update([
                    'is_approved' => true,
                    'application_status' => 'approved',
                    'approved_at' => now(),
                ]);
                break;

            case 'suspend':
                $broker->update([
                    'suspended_at' => now(),
                    'suspended_until' => $request->suspended_until ?? null,
                    'suspension_reason' => $request->reason,
                    'suspended_by' => auth()->id(),
                ]);
                break;

            case 'activate':
                $broker->update([
                    'suspended_at' => null,
                    'suspended_until' => null,
                    'suspension_reason' => null,
                    'suspended_by' => null,
                ]);
                break;

            case 'deactivate':
                $broker->update([
                    'is_approved' => false,
                    'application_status' => 'inactive',
                ]);
                break;
        }

        // Log admin action
        $this->logAdminAction('broker_status_update', $broker, [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Broker status updated successfully.');
    }

    /**
     * Update broker verification status
     */
    public function updateVerification(Request $request, User $broker)
    {
        $request->validate([
            'verification_status' => 'required|in:verified,pending,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $broker->update([
            'verification_status' => $request->verification_status,
            'verified_at' => $request->verification_status === 'verified' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Verification status updated successfully.');
    }

    /**
     * Bulk actions for multiple brokers
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,suspend,activate,send_message',
            'broker_ids' => 'required|array',
            'broker_ids.*' => 'exists:users,id',
            'message' => 'required_if:action,send_message|string|max:1000',
            'reason' => 'nullable|string|max:500',
        ]);

        $brokers = User::whereIn('id', $request->broker_ids)
            ->where('role', 'broker')
            ->get();

        switch ($request->action) {
            case 'approve':
                $brokers->each(function ($broker) {
                    $broker->update([
                        'is_approved' => true,
                        'application_status' => 'approved',
                        'approved_at' => now(),
                    ]);
                });
                break;

            case 'suspend':
                $brokers->each(function ($broker) use ($request) {
                    $broker->update([
                        'suspended_at' => now(),
                        'suspended_until' => $request->suspended_until ?? null,
                        'suspension_reason' => $request->reason,
                        'suspended_by' => auth()->id(),
                    ]);
                });
                break;

            case 'activate':
                $brokers->each(function ($broker) {
                    $broker->update([
                        'suspended_at' => null,
                        'suspended_until' => null,
                        'suspension_reason' => null,
                        'suspended_by' => null,
                    ]);
                });
                break;

            case 'send_message':
                // Send message to selected brokers
                $this->sendBulkMessage($brokers, $request);
                break;
        }

        return redirect()->back()->with('success', 'Bulk action completed successfully.');
    }

    /**
     * Show broker properties
     */
    public function properties(User $broker)
    {
        $properties = $broker->properties()
            ->with(['inquiries', 'transactions'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Brokers/Properties', [
            'broker' => $broker,
            'properties' => $properties,
        ]);
    }

    /**
     * Show broker transactions
     */
    public function transactions(User $broker)
    {
        $transactions = $broker->transactions()
            ->with(['property', 'client'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Brokers/Transactions', [
            'broker' => $broker,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Export broker applications
     */
    public function exportApplications(Request $request)
    {
        $applications = User::pendingApplications()
            ->with(['approvedBy'])
            ->get();

        // Generate CSV export
        $filename = 'broker_applications_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'PRC ID', 'Status', 
                'Applied At', 'Reviewed At', 'Approved By'
            ]);

            // CSV data
            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->id,
                    $app->name,
                    $app->email,
                    $app->phone,
                    $app->prc_id,
                    $app->application_status,
                    $app->created_at->format('Y-m-d H:i:s'),
                    $app->reviewed_at?->format('Y-m-d H:i:s'),
                    $app->approvedBy?->name,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Verify broker document
     */
    public function verifyDocument(Request $request)
    {
        $request->validate([
            'broker_id' => 'required|exists:users,id',
            'document_type' => 'required|in:prc_id,business_permit,additional',
            'verified' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $broker = User::findOrFail($request->broker_id);
        
        $fieldMap = [
            'prc_id' => 'prc_verified',
            'business_permit' => 'business_permit_verified',
            'additional' => 'additional_documents_verified',
        ];

        $verifiedField = $fieldMap[$request->document_type];
        $notesField = str_replace('_verified', '_verification_notes', $verifiedField);

        $broker->update([
            $verifiedField => $request->verified,
            $notesField => $request->notes,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document verification updated successfully.',
        ]);
    }

    /**
     * Generate document report for broker
     */
    public function documentReport(User $broker)
    {
        $report = [
            'broker' => $broker,
            'documents' => [
                'prc_id' => [
                    'verified' => $broker->prc_verified,
                    'notes' => $broker->prc_verification_notes,
                    'verified_at' => $broker->prc_verified_at,
                ],
                'business_permit' => [
                    'verified' => $broker->business_permit_verified,
                    'notes' => $broker->business_permit_verification_notes,
                    'verified_at' => $broker->business_permit_verified_at,
                ],
                'additional_documents' => [
                    'verified' => $broker->additional_documents_verified,
                    'notes' => $broker->additional_documents_verification_notes,
                    'verified_at' => $broker->additional_documents_verified_at,
                ],
            ],
            'verification_status' => $this->getVerificationStatus($broker),
        ];

        return Inertia::render('Admin/Brokers/DocumentReport', $report);
    }

    /**
     * Send communication to broker
     */
    public function sendCommunication(Request $request)
    {
        $request->validate([
            'broker_ids' => 'required|array',
            'broker_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:email,notification,both',
        ]);

        // Implement communication sending logic
        // This would integrate with your notification system
        
        return response()->json([
            'success' => true,
            'message' => 'Communication sent successfully.',
        ]);
    }

    /**
     * Draft communication for broker
     */
    public function draftCommunication(Request $request)
    {
        $request->validate([
            'broker_ids' => 'required|array',
            'broker_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Implement draft saving logic
        
        return response()->json([
            'success' => true,
            'message' => 'Communication drafted successfully.',
        ]);
    }

    /**
     * Schedule communication for broker
     */
    public function scheduleCommunication(Request $request)
    {
        $request->validate([
            'broker_ids' => 'required|array',
            'broker_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'scheduled_at' => 'required|date|after:now',
        ]);

        // Implement scheduling logic
        
        return response()->json([
            'success' => true,
            'message' => 'Communication scheduled successfully.',
        ]);
    }

    /**
     * Store communication template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:email,notification',
        ]);

        // Implement template storage logic
        
        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully.',
        ]);
    }

    /**
     * Send bulk message to brokers
     */
    private function sendBulkMessage($brokers, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'subject' => 'nullable|string|max:255',
        ]);

        foreach ($brokers as $broker) {
            // Send notification to broker
            // TODO: Implement proper notification system
            // For now, we'll just log the action
            $this->logAdminAction('bulk_message_sent', $broker, [
                'subject' => $request->subject ?? 'Admin Message',
                'message' => $request->message,
            ]);
        }
    }

    /**
     * Calculate average response time for broker
     */
    private function calculateAverageResponseTime(User $broker)
    {
        $inquiries = \App\Models\Inquiry::whereHas('property', function($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->whereNotNull('responded_at')->get();

        if ($inquiries->isEmpty()) {
            return 0;
        }

        $totalHours = $inquiries->sum(function($inquiry) {
            return $inquiry->created_at->diffInHours($inquiry->responded_at);
        });

        return round($totalHours / $inquiries->count(), 1);
    }

    /**
     * Calculate client satisfaction for broker
     */
    private function calculateClientSatisfaction(User $broker)
    {
        // This would typically come from client feedback/ratings
        // For now, we'll use a simple calculation based on completed transactions
        $completedTransactions = $broker->transactions()->where('status', 'finalized')->count();
        $totalTransactions = $broker->transactions()->count();

        if ($totalTransactions === 0) {
            return 0;
        }

        // Base satisfaction on completion rate
        $completionRate = $completedTransactions / $totalTransactions;
        return round(($completionRate * 5), 1); // Scale to 5.0
    }

    /**
     * Calculate conversion rate for broker
     */
    private function calculateConversionRate(User $broker)
    {
        $totalInquiries = \App\Models\Inquiry::whereHas('property', function($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->count();

        $completedTransactions = $broker->transactions()->where('status', 'finalized')->count();

        if ($totalInquiries === 0) {
            return 0;
        }

        return round(($completedTransactions / $totalInquiries) * 100, 1);
    }

    /**
     * Log admin action
     */
    private function logAdminAction($action, $target, $details = [])
    {
        \App\Models\AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target_type' => get_class($target),
            'target_id' => $target->id,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get verification status for broker
     */
    private function getVerificationStatus(User $broker)
    {
        $verified = $broker->prc_verified && $broker->business_permit_verified;
        $partial = $broker->prc_verified || $broker->business_permit_verified;

        if ($verified) return 'fully_verified';
        if ($partial) return 'partially_verified';
        return 'unverified';
    }
}