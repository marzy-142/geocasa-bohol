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
        $query = User::where('role', 'broker')
            ->with(['properties', 'clients', 'transactions'])
            ->withCount([
                'properties',
                'clients',
                'transactions as completed_transactions_count' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ])
            ->withSum([
                'transactions as total_commission' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ], 'commission_amount');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true)
                      ->where('application_status', 'approved');
            } elseif ($request->status === 'pending') {
                $query->where('application_status', 'pending');
            } elseif ($request->status === 'suspended') {
                $query->whereNotNull('suspended_at')
                      ->where(function($q) {
                          $q->whereNull('suspended_until')
                            ->orWhere('suspended_until', '>', now());
                      });
            } elseif ($request->status === 'rejected') {
                $query->where('application_status', 'rejected');
            }
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('performance_rating')) {
            // Filter by performance rating if implemented
        }

        $brokers = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate stats
        $stats = [
            'total' => User::where('role', 'broker')->count(),
            'approved' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->where('application_status', 'approved')
                ->count(),
            'pending' => User::where('role', 'broker')
                ->where('application_status', 'pending')
                ->count(),
            'suspended' => User::where('role', 'broker')
                ->whereNotNull('suspended_at')
                ->where(function($query) {
                    $query->whereNull('suspended_until')
                          ->orWhere('suspended_until', '>', now());
                })
                ->count(),
        ];

        return Inertia::render('Admin/Brokers/Index', [
            'brokers' => $brokers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'verification_status', 'performance_rating']),
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

        // Calculate performance metrics
        $performanceMetrics = [
            'total_properties' => $broker->properties()->count(),
            'active_properties' => $broker->properties()->where('status', 'available')->count(),
            'total_clients' => $broker->clients()->count(),
            'active_clients' => $broker->clients()->where('status', 'active')->count(),
            'total_transactions' => $broker->transactions()->count(),
            'completed_transactions' => $broker->transactions()->where('status', 'finalized')->count(),
            'total_commission' => $broker->transactions()->where('status', 'finalized')->sum('commission_amount'),
            'avg_response_time' => 24, // Mock data - implement actual calculation
            'client_satisfaction' => 4.5, // Mock data - implement actual calculation
            'conversion_rate' => 15.5, // Mock data - implement actual calculation
        ];

        // Get recent activities (mock data for now)
        $recentActivities = [
            [
                'type' => 'property_listed',
                'description' => 'Listed new property in Tagbilaran',
                'date' => now()->subDays(1)->format('M d, Y'),
                'icon' => 'home'
            ],
            [
                'type' => 'client_assigned',
                'description' => 'New client assigned',
                'date' => now()->subDays(3)->format('M d, Y'),
                'icon' => 'user'
            ],
        ];

        return Inertia::render('Admin/Brokers/Show', [
            'broker' => $broker,
            'properties' => [
                'total' => $performanceMetrics['total_properties'],
                'active' => $performanceMetrics['active_properties'],
                'data' => $broker->properties
            ],
            'clients' => [
                'total' => $performanceMetrics['total_clients'],
                'active' => $performanceMetrics['active_clients'],
                'data' => $broker->clients
            ],
            'transactions' => [
                'total' => $performanceMetrics['total_transactions'],
                'completed' => $performanceMetrics['completed_transactions'],
                'data' => $broker->transactions
            ],
            'performance' => $performanceMetrics,
            'recentActivities' => $recentActivities,
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
            'prc_id' => 'nullable|string|max:255',
            'business_permit' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $broker->update($request->only([
            'name',
            'email',
            'prc_id',
            'business_permit',
            'admin_notes',
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

        // Log admin action if admin_notes provided
        if ($request->admin_notes) {
            // Implement admin action logging
        }

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
                // Implement bulk messaging functionality
                break;
        }

        return redirect()->back()->with('success', 'Bulk action completed successfully.');
    }
}