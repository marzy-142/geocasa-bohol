<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrokerClientAnalyticsService;
use App\Services\UnifiedBrokerAssignmentService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokerAnalyticsController extends Controller
{
    protected BrokerClientAnalyticsService $analyticsService;
    protected UnifiedBrokerAssignmentService $assignmentService;

    public function __construct(
        BrokerClientAnalyticsService $analyticsService,
        UnifiedBrokerAssignmentService $assignmentService
    ) {
        $this->analyticsService = $analyticsService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * Get broker performance metrics
     */
    public function getPerformanceMetrics(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $days = $request->get('days', 30);

        // Check if user can access broker metrics
        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $metrics = $this->analyticsService->getBrokerPerformanceMetrics($brokerId, $days);

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve broker metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker workload information
     */
    public function getWorkload(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();

        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $workload = $this->analyticsService->getBrokerWorkload($brokerId);

            return response()->json([
                'success' => true,
                'data' => $workload
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve broker workload',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker relationship analytics
     */
    public function getRelationshipAnalytics(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $days = $request->get('days', 30);

        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $analytics = $this->analyticsService->getBrokerRelationshipAnalytics($brokerId, $days);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve relationship analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get communication effectiveness metrics
     */
    public function getCommunicationEffectiveness(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $days = $request->get('days', 30);

        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $effectiveness = $this->analyticsService->getCommunicationEffectiveness($brokerId, $days);

            return response()->json([
                'success' => true,
                'data' => $effectiveness
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve communication effectiveness',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive broker performance report
     */
    public function generatePerformanceReport(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $days = $request->get('days', 30);

        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $report = $this->analyticsService->generateBrokerPerformanceReport($brokerId, $days);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker assignment recommendations
     */
    public function getAssignmentRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|in:client,inquiry',
            'entity_id' => 'required|integer',
            'context' => 'nullable|string',
        ]);

        try {
            $entityType = $request->get('entity_type');
            $entityId = $request->get('entity_id');
            $context = $request->get('context', 'general');

            // Get the entity
            $entity = $this->getEntity($entityType, $entityId);
            
            if (!$entity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entity not found'
                ], 404);
            }

            $recommendations = $this->assignmentService->getBrokerRecommendations($entity, [
                'context' => $context,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assignment recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all brokers with their performance summary
     */
    public function getAllBrokersPerformance(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $days = $request->get('days', 30);
        $limit = $request->get('limit', 50);

        try {
            $brokers = User::where('role', 'broker')
                ->where('status', 'approved')
                ->where('is_active', true)
                ->limit($limit)
                ->get();

            $brokerPerformance = $brokers->map(function ($broker) use ($days) {
                $metrics = $this->analyticsService->getBrokerPerformanceMetrics($broker->id, $days);
                
                return [
                    'broker' => $broker->only(['id', 'name', 'email']),
                    'efficiency_score' => $metrics['efficiency_score'] ?? 0,
                    'workload' => $metrics['workload_metrics']['current_workload'] ?? [],
                    'conversion_rate' => $metrics['conversion_metrics']['overall_conversion_rate'] ?? 0,
                    'response_time' => $metrics['performance_metrics']['response_times']['average_response_time_minutes'] ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $brokerPerformance->sortByDesc('efficiency_score')->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brokers performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker performance trends
     */
    public function getPerformanceTrends(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $days = $request->get('days', 30);

        if (!$this->canAccessBrokerMetrics(Auth::user(), $brokerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to broker metrics'
            ], 403);
        }

        try {
            $trends = $this->analyticsService->getBrokerPerformanceMetrics($brokerId, $days);
            $trendData = $trends['trends'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $trendData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can access broker metrics
     */
    protected function canAccessBrokerMetrics(User $user, int $brokerId): bool
    {
        // Admin can access all broker metrics
        if ($user->isAdmin()) {
            return true;
        }

        // Broker can only access their own metrics
        if ($user->isBroker() && $user->id === $brokerId) {
            return true;
        }

        return false;
    }

    /**
     * Get entity by type and ID
     */
    protected function getEntity(string $type, int $id)
    {
        return match($type) {
            'client' => \App\Models\Client::find($id),
            'inquiry' => \App\Models\Inquiry::find($id),
            default => null
        };
    }
}

