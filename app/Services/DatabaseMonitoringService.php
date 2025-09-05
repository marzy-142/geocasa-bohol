<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DatabaseMonitoringService
{
    /**
     * Slow query threshold in milliseconds
     */
    const SLOW_QUERY_THRESHOLD = 1000; // 1 second
    
    /**
     * Cache duration for monitoring data
     */
    const CACHE_DURATION = 300; // 5 minutes

    /**
     * Start monitoring database queries
     */
    public function startMonitoring()
    {
        DB::listen(function ($query) {
            $this->logQuery($query);
            
            if ($query->time > self::SLOW_QUERY_THRESHOLD) {
                $this->logSlowQuery($query);
            }
        });
    }

    /**
     * Log query execution
     */
    public function logQuery(string $sql, array $bindings, float $time): void
    {
        // Replace bindings in SQL for better readability
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        
        Log::channel('database')->info('Query executed', [
            'sql' => $sql,
            'time' => $time,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log slow queries
     */
    private function logSlowQuery($query)
    {
        $sql = $query->sql;
        $bindings = $query->bindings;
        $time = $query->time;
        
        // Replace bindings in SQL
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        
        Log::channel('slow_queries')->warning('Slow query detected', [
            'sql' => $sql,
            'time' => $time,
            'threshold' => self::SLOW_QUERY_THRESHOLD,
            'timestamp' => now()->toISOString(),
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
        ]);
        
        // Store in cache for dashboard display
        $this->storeSlowQueryMetric($sql, $time);
    }

    /**
     * Store slow query metrics in cache
     */
    private function storeSlowQueryMetric($sql, $time)
    {
        $slowQueries = Cache::get('slow_queries_metrics', []);
        
        $slowQueries[] = [
            'sql' => $sql,
            'time' => $time,
            'timestamp' => now()->toISOString(),
        ];
        
        // Keep only last 100 slow queries
        if (count($slowQueries) > 100) {
            $slowQueries = array_slice($slowQueries, -100);
        }
        
        Cache::put('slow_queries_metrics', $slowQueries, self::CACHE_DURATION);
    }

    /**
     * Get database performance metrics
     */
    public function getPerformanceMetrics()
    {
        return Cache::remember('db_performance_metrics', self::CACHE_DURATION, function () {
            $metrics = [];
            
            // Test connection time
            $start = microtime(true);
            try {
                DB::connection()->getPdo();
                $connectionTime = (microtime(true) - $start) * 1000;
                $metrics['connection_time_ms'] = round($connectionTime, 2);
                $metrics['connection_status'] = 'connected';
            } catch (\Exception $e) {
                $metrics['connection_time_ms'] = null;
                $metrics['connection_status'] = 'failed';
                $metrics['connection_error'] = $e->getMessage();
            }
            
            // Test query performance
            $start = microtime(true);
            try {
                DB::table('users')->count();
                $queryTime = (microtime(true) - $start) * 1000;
                $metrics['query_time_ms'] = round($queryTime, 2);
            } catch (\Exception $e) {
                $metrics['query_time_ms'] = null;
                $metrics['query_error'] = $e->getMessage();
            }
            
            // Get database size information
            $metrics['database_info'] = $this->getDatabaseInfo();
            
            // Get slow queries count
            $metrics['slow_queries_count'] = count(Cache::get('slow_queries_metrics', []));
            
            return $metrics;
        });
    }

    /**
     * Get database information
     */
    private function getDatabaseInfo()
    {
        try {
            $databaseName = DB::connection()->getDatabaseName();
            
            // Get table information
            $tables = DB::select("SELECT 
                TABLE_NAME as name,
                TABLE_ROWS as rows,
                ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ? 
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC", [$databaseName]);
            
            return [
                'database_name' => $databaseName,
                'tables' => $tables,
                'total_size_mb' => array_sum(array_column($tables, 'size_mb')),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Could not retrieve database information: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get slow queries from cache
     */
    public function getSlowQueries($limit = 20)
    {
        $slowQueries = Cache::get('slow_queries_metrics', []);
        
        // Sort by time descending and limit
        usort($slowQueries, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        
        return array_slice($slowQueries, 0, $limit);
    }

    /**
     * Analyze query patterns
     */
    public function analyzeQueryPatterns()
    {
        $slowQueries = Cache::get('slow_queries_metrics', []);
        
        if (empty($slowQueries)) {
            return [];
        }
        
        $patterns = [];
        
        foreach ($slowQueries as $query) {
            // Extract table names from SQL
            preg_match_all('/(?:FROM|JOIN|UPDATE|INSERT INTO)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $query['sql'], $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $table) {
                    if (!isset($patterns[$table])) {
                        $patterns[$table] = [
                            'count' => 0,
                            'total_time' => 0,
                            'avg_time' => 0,
                            'max_time' => 0,
                        ];
                    }
                    
                    $patterns[$table]['count']++;
                    $patterns[$table]['total_time'] += $query['time'];
                    $patterns[$table]['max_time'] = max($patterns[$table]['max_time'], $query['time']);
                }
            }
        }
        
        // Calculate averages
        foreach ($patterns as $table => &$data) {
            $data['avg_time'] = round($data['total_time'] / $data['count'], 2);
        }
        
        // Sort by average time descending
        uasort($patterns, function ($a, $b) {
            return $b['avg_time'] <=> $a['avg_time'];
        });
        
        return $patterns;
    }

    /**
     * Get optimization suggestions
     */
    public function getOptimizationSuggestions()
    {
        $suggestions = [];
        $patterns = $this->analyzeQueryPatterns();
        
        foreach ($patterns as $table => $data) {
            if ($data['avg_time'] > 500) { // 500ms threshold
                $suggestions[] = [
                    'type' => 'slow_table',
                    'message' => "Table '{$table}' has slow queries (avg: {$data['avg_time']}ms). Consider adding indexes.",
                    'priority' => 'high',
                    'table' => $table,
                    'avg_time' => $data['avg_time'],
                ];
            }
        }
        
        // Check for missing indexes based on common patterns
        $slowQueries = $this->getSlowQueries(50);
        
        foreach ($slowQueries as $query) {
            // Look for WHERE clauses without indexes
            if (preg_match('/WHERE\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*=/i', $query['sql'], $matches)) {
                $column = $matches[1];
                $suggestions[] = [
                    'type' => 'missing_index',
                    'message' => "Consider adding an index on column '{$column}' for better performance.",
                    'priority' => 'medium',
                    'column' => $column,
                    'query_time' => $query['time'],
                ];
            }
        }
        
        return array_unique($suggestions, SORT_REGULAR);
    }

    /**
     * Clear monitoring data
     */
    public function clearMonitoringData()
    {
        Cache::forget('slow_queries_metrics');
        Cache::forget('db_performance_metrics');
    }

    /**
     * Export monitoring data for analysis
     */
    public function exportMonitoringData()
    {
        return [
            'performance_metrics' => $this->getPerformanceMetrics(),
            'slow_queries' => $this->getSlowQueries(100),
            'query_patterns' => $this->analyzeQueryPatterns(),
            'optimization_suggestions' => $this->getOptimizationSuggestions(),
            'exported_at' => now()->toISOString(),
        ];
    }
}