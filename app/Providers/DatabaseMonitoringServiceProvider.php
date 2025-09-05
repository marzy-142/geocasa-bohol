<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;

class DatabaseMonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!config('database-monitoring.enabled')) {
            return;
        }

        $this->setupQueryLogging();
        $this->setupSlowQueryLogging();
    }

    /**
     * Set up general query logging
     */
    protected function setupQueryLogging(): void
    {
        if (!config('database-monitoring.query_logging.enabled')) {
            return;
        }

        DB::listen(function (QueryExecuted $query) {
            $logChannel = config('database-monitoring.query_logging.log_channel', 'database');
            $includeBindings = config('database-monitoring.query_logging.include_bindings', true);

            $logData = [
                'sql' => $query->sql,
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];

            if ($includeBindings) {
                $logData['bindings'] = $query->bindings;
            }

            Log::channel($logChannel)->info('Database Query Executed', $logData);
        });
    }

    /**
     * Set up slow query logging
     */
    protected function setupSlowQueryLogging(): void
    {
        $threshold = config('database-monitoring.slow_query_threshold', 1000);
        
        if ($threshold <= 0) {
            return;
        }

        DB::listen(function (QueryExecuted $query) use ($threshold) {
            if ($query->time >= $threshold) {
                $this->logSlowQuery($query);
            }
        });
    }

    /**
     * Log slow query with detailed information
     */
    protected function logSlowQuery(QueryExecuted $query): void
    {
        $logData = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'connection' => $query->connectionName,
            'threshold' => config('database-monitoring.slow_query_threshold'),
            'request_url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];

        Log::channel('slow-queries')->warning('Slow Database Query Detected', $logData);

        // Trigger alert if enabled
        if (config('database-monitoring.alerts.enabled')) {
            $this->triggerSlowQueryAlert($logData);
        }
    }

    /**
     * Trigger alert for slow query
     */
    protected function triggerSlowQueryAlert(array $queryData): void
    {
        // This could be expanded to send notifications via email, Slack, etc.
        // For now, we'll just log it as a critical alert
        Log::channel('alerts')->critical('Slow Query Alert', [
            'message' => 'Database query exceeded performance threshold',
            'query_time' => $queryData['time'],
            'threshold' => $queryData['threshold'],
            'sql' => $queryData['sql'],
            'url' => $queryData['request_url'],
        ]);
    }
}