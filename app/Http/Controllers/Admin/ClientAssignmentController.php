<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerRequest;
use App\Models\User;
use App\Notifications\BrokerSellerAssignmentNotification;
use App\Mail\SellerBrokerAssignedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ClientAssignmentController extends Controller
{
    /**
     * Display the seller-broker assignment management page
     */
    public function index(Request $request)
    {
        // Ensure only admins can access this
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can manage seller-broker assignments.');
        }

        // Build query for seller requests
        $query = SellerRequest::with(['assignedBroker', 'property']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('seller_name', 'like', "%{$search}%")
                  ->orWhere('seller_email', 'like', "%{$search}%")
                  ->orWhere('seller_phone', 'like', "%{$search}%")
                  ->orWhere('property_title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'unassigned') {
                $query->whereNull('assigned_broker_id');
            } elseif ($request->assignment_status === 'assigned') {
                $query->whereNotNull('assigned_broker_id');
            }
        }

        if ($request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get seller requests with pagination
        $sellerRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all approved brokers with seller request counts
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
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
     * Get broker performance analytics for seller requests
     */
    public function getBrokerAnalytics(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can view broker analytics.');
        }

        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'assignedSellerRequests',
                'assignedSellerRequests as pending_seller_requests_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'assignedSellerRequests as approved_seller_requests_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'assignedSellerRequests as listed_properties_count' => function ($query) {
                    $query->whereNotNull('property_id');
                }
            ])
            ->orderByDesc('assigned_seller_requests_count')
            ->get()
            ->map(function ($broker) {
                $listingRate = $broker->assigned_seller_requests_count > 0 
                    ? round(($broker->listed_properties_count / $broker->assigned_seller_requests_count) * 100, 1)
                    : 0;

                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'total_seller_requests' => $broker->assigned_seller_requests_count,
                    'pending_requests' => $broker->pending_seller_requests_count,
                    'approved_requests' => $broker->approved_seller_requests_count,
                    'listed_properties' => $broker->listed_properties_count,
                    'listing_rate' => $listingRate,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brokers,
        ]);
    }

    /**
     * Get assignment recommendations based on broker workload and performance for seller requests
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
                // 3. Location proximity (if available)
                
                $workloadScore = max(0, 100 - ($broker->pending_seller_requests_count * 15)); // Penalize high workload
                $performanceScore = $broker->assigned_seller_requests_count > 0 
                    ? ($broker->listed_properties_count / $broker->assigned_seller_requests_count) * 100
                    : 50; // Default score for new brokers
                
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
     * Assign broker to seller request
     */
    public function assign(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can assign brokers to seller requests.');
        }

        $validated = $request->validate([
            'seller_request_id' => 'required|exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $sellerRequest = SellerRequest::findOrFail($validated['seller_request_id']);
        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->firstOrFail();

        $wasAssigned = $sellerRequest->assigned_broker_id !== null;
        $action = $wasAssigned ? 'reassigned' : 'assigned';

        $sellerRequest->update([
            'assigned_broker_id' => $broker->id,
        ]);

        // Send notification to the broker
        $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, Auth::user(), $action));

        // Send email to the seller
        if ($sellerRequest->seller_email) {
            Mail::to($sellerRequest->seller_email)
                ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, Auth::user()));
        }

        return redirect()->back()->with('success', 'Broker assigned successfully to seller request.');
    }

    /**
     * Bulk assign broker to multiple seller requests
     */
    public function bulkAssign(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can bulk assign brokers to seller requests.');
        }

        $validated = $request->validate([
            'seller_request_ids' => 'required|array|min:1',
            'seller_request_ids.*' => 'exists:seller_requests,id',
            'broker_id' => 'required|exists:users,id',
        ]);

        $broker = User::where('id', $validated['broker_id'])
            ->where('role', 'broker')
            ->where('is_approved', true)
            ->firstOrFail();

        // Get seller requests before updating to check assignment status
        $sellerRequests = SellerRequest::whereIn('id', $validated['seller_request_ids'])->get();

        SellerRequest::whereIn('id', $validated['seller_request_ids'])
            ->update(['assigned_broker_id' => $broker->id]);

        // Send notifications for each seller request
        foreach ($sellerRequests as $sellerRequest) {
            $wasAssigned = $sellerRequest->assigned_broker_id !== null;
            $action = $wasAssigned ? 'reassigned' : 'assigned';

            // Send notification to the broker
            $broker->notify(new BrokerSellerAssignmentNotification($sellerRequest, Auth::user(), $action));

            // Send email to the seller
            if ($sellerRequest->seller_email) {
                Mail::to($sellerRequest->seller_email)
                    ->send(new SellerBrokerAssignedMail($sellerRequest, $broker, Auth::user()));
            }
        }

        $count = count($validated['seller_request_ids']);
        return redirect()->back()->with('success', "Successfully assigned {$count} seller requests to {$broker->name}.");
    }

    /**
     * Get workload level description for seller requests
     */
    private function getWorkloadLevel($pendingRequests)
    {
        if ($pendingRequests <= 3) {
            return 'Light';
        } elseif ($pendingRequests <= 6) {
            return 'Moderate';
        } elseif ($pendingRequests <= 10) {
            return 'Heavy';
        } else {
            return 'Overloaded';
        }
    }
}