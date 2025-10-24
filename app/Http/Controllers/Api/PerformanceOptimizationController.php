<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ComprehensivePerformanceOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PerformanceOptimizationController extends Controller
{
    protected ComprehensivePerformanceOptimizationService $optimizationService;

    public function __construct(ComprehensivePerformanceOptimizationService $optimizationService)
    {
        $this->optimizationService = $optimizationService;
    }

    /**
     * Get comprehensive performance statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->optimizationService->getComprehensiveStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve performance statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run comprehensive optimization
     */
    public function runOptimization(Request $request): JsonResponse
    {
        try {
            $clearCache = $request->boolean('clear_cache', false);
            $warmCache = $request->boolean('warm_cache', false);
            
            if ($clearCache) {
                $this->optimizationService->clearAllCaches();
            }
            
            $results = $this->optimizationService->runComprehensiveOptimization();
            
            if ($warmCache) {
                $this->optimizationService->warmUpAllCaches();
            }
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to run optimization: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to run optimization',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get optimization recommendations
     */
    public function getRecommendations(): JsonResponse
    {
        try {
            $summary = $this->optimizationService->getOptimizationSummary();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => $summary['recommendations'],
                    'health_status' => $summary['health_status'],
                    'overall_health' => $summary['overall_health'],
                ],
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get recommendations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve recommendations',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get health status
     */
    public function getHealth(): JsonResponse
    {
        try {
            $summary = $this->optimizationService->getOptimizationSummary();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'overall_health' => $summary['overall_health'],
                    'health_status' => $summary['health_status'],
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get health status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve health status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $summary = $this->optimizationService->getOptimizationSummary();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'performance_metrics' => $summary['performance_metrics'],
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve performance metrics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all caches
     */
    public function clearCaches(): JsonResponse
    {
        try {
            $results = $this->optimizationService->clearAllCaches();
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear caches: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear caches',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up all caches
     */
    public function warmUpCaches(): JsonResponse
    {
        try {
            $results = $this->optimizationService->warmUpAllCaches();
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to warm up caches: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to warm up caches',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get optimization summary
     */
    public function getSummary(): JsonResponse
    {
        try {
            $summary = $this->optimizationService->getOptimizationSummary();
            
            return response()->json([
                'success' => true,
                'data' => $summary,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get optimization summary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve optimization summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
