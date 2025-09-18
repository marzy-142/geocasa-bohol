<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\SellerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientAssignmentController extends Controller
{
    /**
     * Display client assignment management page
     */
    public function index(Request $request)
    {
        $query = SellerRequest::with(['user', 'assignedBroker', 'property'])
            ->select('id', 'user_id', 'assigned_broker_id', 'property_id', 'status', 'created_at', 'updated_at');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('assigned_broker_id');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('assigned_broker_id');
            }
        }

        if ($request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sellerRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all approved brokers with seller request counts
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->withCount(['assignedSellerRequests' => function($query) {
                $query->whereNotNull('assigned_broker_id');
            }])
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $stats = $this->getAssignmentStats();

        return Inertia::render('Admin/ClientAssignments', [
            'sellerRequests' => $sellerRequests,
            'brokers' => $brokers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'assignment_status', 'broker_id', 'status']),
        ]);
    }

    /**
     * Get seller assignment statistics
     */
    private function getAssignmentStats()
    {
        $totalSellerRequests = SellerRequest::count();
        $unassignedSellerRequests = SellerRequest::whereNull('assigned_broker_id')->count();
        $assignedSellerRequests = SellerRequest::whereNotNull('assigned_broker_id')->count();
        $activeBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->count();

        $avgSellerRequestsPerBroker = $activeBrokers > 0 
            ? round($assignedSellerRequests / $activeBrokers, 1)
            : 0;

        return [
            'total' => $totalSellerRequests,
            'unassigned' => $unassignedSellerRequests,
            'assigned' => $assignedSellerRequests,
            'activeBrokers' => $activeBrokers,
            'avgSellerRequestsPerBroker' => $avgSellerRequestsPerBroker,
        ];
    }

    /**
     * Get broker performance analytics
     */
    public function getBrokerAnalytics(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view broker analytics.');
        }

        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->withCount([
                'clients as total_clients',
                'clients as active_clients_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'transactions as completed_transactions' => function ($query) {
                    $query->where('status', 'finalized');
                },
                'transactions as total_transactions'
            ])
            ->withSum('transactions as total_sales_value', 'total_amount')
            ->orderByDesc('completed_transactions')
            ->get()
            ->map(function ($broker) {
                $conversionRate = $broker->total_clients > 0 
                    ? round(($broker->completed_transactions / $broker->total_clients) * 100, 1)
                    : 0;

                $avgDealValue = $broker->completed_transactions > 0 
                    ? round($broker->total_sales_value / $broker->completed_transactions, 2)
                    : 0;

                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'total_clients' => $broker->total_clients,
                    'active_clients' => $broker->active_clients_count,
                    'completed_transactions' => $broker->completed_transactions,
                    'total_transactions' => $broker->total_transactions,
                    'total_sales_value' => $broker->total_sales_value ?: 0,
                    'formatted_sales_value' => '₱' . number_format($broker->total_sales_value ?: 0, 2),
                    'conversion_rate' => $conversionRate,
                    'avg_deal_value' => $avgDealValue,
                    'formatted_avg_deal_value' => '₱' . number_format($avgDealValue, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brokers,
            'message' => 'Broker analytics retrieved successfully'
        ]);
    }

    /**
     * Get assignment recommendations based on broker workload and performance
     */
    public function getAssignmentRecommendations(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view assignment recommendations.');
        }

        $validated = $request->validate([
            'seller_request_id' => 'required|exists:seller_requests,id',
        ]);

        $sellerRequest = SellerRequest::findOrFail($validated['seller_request_id']);

        // Get brokers with their current workload and performance metrics
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->withCount([
                'assignedSellerRequests',
                'assignedSellerRequests as pending_seller_requests_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'assignedSellerRequests as listed_properties_count' => function ($query) {
                    $query->whereNotNull('property_id');
                }
            ])
            ->get()
            ->map(function ($broker) use ($sellerRequest) {
                // Calculate recommendation score based on:
                // 1. Current workload (lower is better)
                // 2. Performance history (listing success rate)
                $workloadScore = max(0, 100 - ($broker->pending_seller_requests_count * 10));
                $performanceScore = $broker->assigned_seller_requests_count > 0 
                    ? ($broker->listed_properties_count / $broker->assigned_seller_requests_count) * 100
                    : 50;
                
                $totalScore = ($workloadScore * 0.6) + ($performanceScore * 0.4);

                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'current_seller_requests' => $broker->assigned_seller_requests_count,
                    'pending_requests' => $broker->pending_seller_requests_count,
                    'listed_properties' => $broker->listed_properties_count,
                    'recommendation_score' => round($totalScore, 1),
                    'workload_level' => $this->getWorkloadLevel($broker->pending_seller_requests_count),
                ];
            })
            ->sortByDesc('recommendation_score')
            ->values();

        return response()->json([
            'success' => true,
            'sellerRequest' => $sellerRequest,
            'recommendations' => $brokers,
        ]);
    }

    /**
     * Assign a broker to a seller request
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'seller_request_id' => 'required|exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $sellerRequest = SellerRequest::findOrFail($validated['seller_request_id']);
        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->firstOrFail();

        $sellerRequest->update([
            'assigned_broker_id' => $broker->id,
            'assigned_at' => now(),
        ]);

        return redirect()->back()->with('success', "Seller request assigned to {$broker->name} successfully.");
    }

    /**
     * Bulk assign broker to multiple seller requests
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'seller_request_ids' => 'required|array',
            'seller_request_ids.*' => 'exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->firstOrFail();

        $updated = SellerRequest::whereIn('id', $validated['seller_request_ids'])
            ->update([
                'assigned_broker_id' => $broker->id,
                'assigned_at' => now(),
            ]);

        return redirect()->back()->with('success', "{$updated} seller requests assigned to {$broker->name} successfully.");
    }

    /**
     * Get workload level description
     */
    private function getWorkloadLevel($pendingCount)
    {
        if ($pendingCount <= 2) return 'Light';
        if ($pendingCount <= 5) return 'Moderate';
        if ($pendingCount <= 8) return 'Heavy';
        return 'Overloaded';
    }
}