<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Inquiry;
use App\Models\User;
use Carbon\Carbon;

class DatabaseOptimizationService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60; // 1 hour
    const STATS_CACHE_DURATION = 30; // 30 minutes for stats
    const DASHBOARD_CACHE_DURATION = 15; // 15 minutes for dashboard data

    /**
     * Get cached dashboard statistics for brokers
     */
    public function getBrokerDashboardStats($brokerId)
    {
        return Cache::remember("broker_dashboard_stats_{$brokerId}", self::DASHBOARD_CACHE_DURATION, function () use ($brokerId) {
            return [
                'totalProperties' => Property::where('broker_id', $brokerId)->count(),
                'activeProperties' => Property::where('broker_id', $brokerId)->where('status', 'available')->count(),
                'totalClients' => DB::table('clients')->where('broker_id', $brokerId)->count(),
                'activeInquiries' => DB::table('inquiries')
                    ->join('properties', 'inquiries.property_id', '=', 'properties.id')
                    ->where('properties.broker_id', $brokerId)
                    ->where('inquiries.status', 'pending')
                    ->count(),
                'completedTransactions' => Transaction::where('broker_id', $brokerId)
                    ->where('status', 'completed')
                    ->count(),
                'totalCommission' => Transaction::where('broker_id', $brokerId)
                    ->where('status', 'completed')
                    ->sum('commission_amount'),
            ];
        });
    }

    /**
     * Get cached property statistics
     */
    public function getPropertyStats()
    {
        return Cache::remember('property_stats', self::STATS_CACHE_DURATION, function () {
            return [
                'total' => Property::count(),
                'available' => Property::where('status', 'available')->count(),
                'sold' => Property::where('status', 'sold')->count(),
                'reserved' => Property::where('status', 'reserved')->count(),
                'by_municipality' => Property::select('municipality', DB::raw('count(*) as count'))
                    ->groupBy('municipality')
                    ->orderBy('count', 'desc')
                    ->get(),
                'by_type' => Property::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get(),
            ];
        });
    }

    /**
     * Get cached broker performance data
     */
    public function getBrokerPerformance()
    {
        return Cache::remember('broker_performance', self::CACHE_DURATION, function () {
            return User::where('role', 'broker')
                ->where('application_status', 'approved')
                ->withCount([
                    'properties',
                    'clients',
                    'transactions',
                    'transactions as completed_transactions_count' => function ($query) {
                        $query->where('status', 'completed');
                    }
                ])
                ->withSum('transactions as total_commission', 'commission_amount')
                ->orderBy('completed_transactions_count', 'desc')
                ->get();
        });
    }

    /**
     * Get cached monthly analytics data
     */
    public function getMonthlyAnalytics($brokerId = null)
    {
        $cacheKey = $brokerId ? "monthly_analytics_{$brokerId}" : 'monthly_analytics_global';
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($brokerId) {
            $monthlyData = collect();
            
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $inquiriesQuery = DB::table('inquiries')
                    ->join('properties', 'inquiries.property_id', '=', 'properties.id')
                    ->whereBetween('inquiries.created_at', [$startOfMonth, $endOfMonth]);
                    
                $transactionsQuery = DB::table('transactions')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    
                $propertiesQuery = DB::table('properties')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                
                if ($brokerId) {
                    $inquiriesQuery->where('properties.broker_id', $brokerId);
                    $transactionsQuery->where('broker_id', $brokerId);
                    $propertiesQuery->where('broker_id', $brokerId);
                }
                
                $monthlyData->push([
                    'month' => $date->format('M Y'),
                    'inquiries' => $inquiriesQuery->count(),
                    'transactions' => $transactionsQuery->count(),
                    'commission' => $transactionsQuery->where('status', 'completed')->sum('commission_amount'),
                    'properties_added' => $propertiesQuery->count(),
                ]);
            }
            
            return $monthlyData;
        });
    }

    /**
     * Get cached filter options for properties
     */
    public function getPropertyFilterOptions($brokerId = null)
    {
        $cacheKey = $brokerId ? "property_filters_{$brokerId}" : 'property_filters_global';
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($brokerId) {
            $query = Property::query();
            
            if ($brokerId) {
                $query->where('broker_id', $brokerId);
            }
            
            return [
                'municipalities' => $query->distinct()->pluck('municipality')->filter()->sort()->values(),
                'types' => $query->distinct()->pluck('type')->filter()->sort()->values(),
                'brokers' => User::where('role', 'broker')
                    ->where('application_status', 'approved')
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get(),
                'price_ranges' => [
                    'min' => $query->min('total_price'),
                    'max' => $query->max('total_price'),
                ],
                'area_ranges' => [
                    'min' => $query->min('lot_area_sqm'),
                    'max' => $query->max('lot_area_sqm'),
                ],
            ];
        });
    }

    /**
     * Clear all cached data
     */
    public function clearAllCache()
    {
        $patterns = [
            'broker_dashboard_stats_*',
            'property_stats',
            'broker_performance',
            'monthly_analytics_*',
            'property_filters_*',
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Clear cache for specific broker
     */
    public function clearBrokerCache($brokerId)
    {
        Cache::forget("broker_dashboard_stats_{$brokerId}");
        Cache::forget("monthly_analytics_{$brokerId}");
        Cache::forget("property_filters_{$brokerId}");
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmUpCache()
    {
        // Warm up global stats
        $this->getPropertyStats();
        $this->getBrokerPerformance();
        $this->getMonthlyAnalytics();
        $this->getPropertyFilterOptions();
        
        // Warm up cache for active brokers
        $activeBrokers = User::where('role', 'broker')
            ->where('application_status', 'approved')
            ->pluck('id');
            
        foreach ($activeBrokers as $brokerId) {
            $this->getBrokerDashboardStats($brokerId);
            $this->getMonthlyAnalytics($brokerId);
            $this->getPropertyFilterOptions($brokerId);
        }
    }

    /**
     * Get database performance metrics
     */
    public function getPerformanceMetrics()
    {
        return Cache::remember('db_performance_metrics', 5, function () {
            $start = microtime(true);
            
            // Test query performance
            DB::table('properties')->count();
            
            $queryTime = (microtime(true) - $start) * 1000;
            
            return [
                'query_time_ms' => round($queryTime, 2),
                'connection_status' => 'connected',
                'cache_hit_rate' => $this->getCacheHitRate(),
                'slow_queries' => $this->getSlowQueries(),
            ];
        });
    }

    /**
     * Get cache hit rate (simplified)
     */
    private function getCacheHitRate()
    {
        // This is a simplified implementation
        // In production, you might want to use Redis INFO or similar
        return 85.5; // Placeholder percentage
    }

    /**
     * Get slow queries (simplified)
     */
    private function getSlowQueries()
    {
        // This would typically query the slow query log
        // For now, return a placeholder
        return [];
    }
}