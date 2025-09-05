<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseMonitoringService;
use App\Services\DatabaseOptimizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DatabaseMonitoringController extends Controller
{
    protected $monitoringService;
    protected $optimizationService;

    public function __construct(
        DatabaseMonitoringService $monitoringService,
        DatabaseOptimizationService $optimizationService
    ) {
        $this->monitoringService = $monitoringService;
        $this->optimizationService = $optimizationService;
    }

    /**
     * Display database monitoring dashboard
     */
    public function index(Request $request)
    {
        // Get performance metrics
        $performanceMetrics = $this->monitoringService->getPerformanceMetrics();
        
        // Get slow queries
        $slowQueries = $this->monitoringService->getSlowQueries();
        
        // Get query patterns analysis
        $queryPatterns = $this->monitoringService->analyzeQueryPatterns();
        
        // Get optimization suggestions
        $suggestions = $this->monitoringService->getOptimizationSuggestions();
        
        // Get cache performance from optimization service
        $cacheMetrics = $this->optimizationService->getPerformanceMetrics();

        return Inertia::render('Admin/DatabaseMonitoring', [
            'performanceMetrics' => $performanceMetrics,
            'slowQueries' => $slowQueries,
            'queryPatterns' => $queryPatterns,
            'suggestions' => $suggestions,
            'cacheMetrics' => $cacheMetrics,
        ]);
    }

    /**
     * Export monitoring data
     */
    public function export(Request $request)
    {
        $data = $this->monitoringService->exportData();
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'exported_at' => now()->toISOString(),
        ]);
    }

    /**
     * Clear monitoring data
     */
    public function clear(Request $request)
    {
        $this->monitoringService->clearData();
        
        return response()->json([
            'success' => true,
            'message' => 'Monitoring data cleared successfully',
        ]);
    }

    /**
     * Warm up cache
     */
    public function warmCache(Request $request)
    {
        $this->optimizationService->warmUpCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Cache warmed up successfully',
        ]);
    }

    /**
     * Clear all cache
     */
    public function clearCache(Request $request)
    {
        $this->optimizationService->clearAllCache();
        
        return response()->json([
            'success' => true,
            'message' => 'All cache cleared successfully',
        ]);
    }

    /**
     * Get real-time metrics via API
     */
    public function metrics(Request $request)
    {
        return response()->json([
            'performance' => $this->monitoringService->getPerformanceMetrics(),
            'cache' => $this->optimizationService->getPerformanceMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}