<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Query Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for database query monitoring
    | and slow query logging to help identify performance bottlenecks.
    |
    */

    'enabled' => env('DB_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Queries that take longer than this threshold (in milliseconds) will be
    | logged as slow queries. Set to 0 to disable slow query logging.
    |
    */
    'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // 1 second

    /*
    |--------------------------------------------------------------------------
    | Query Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable query logging. When enabled, all database queries
    | will be logged to the specified log channel.
    |
    */
    'query_logging' => [
        'enabled' => env('DB_QUERY_LOGGING_ENABLED', false),
        'log_channel' => env('DB_QUERY_LOG_CHANNEL', 'database'),
        'include_bindings' => env('DB_QUERY_LOG_BINDINGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Metrics
    |--------------------------------------------------------------------------
    |
    | Configuration for collecting database performance metrics
    |
    */
    'metrics' => [
        'enabled' => env('DB_METRICS_ENABLED', true),
        'collect_connection_count' => true,
        'collect_query_count' => true,
        'collect_execution_time' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for triggering alerts when database performance degrades
    |
    */
    'alerts' => [
        'enabled' => env('DB_ALERTS_ENABLED', false),
        'slow_query_count_threshold' => 10, // Alert if more than 10 slow queries per minute
        'connection_count_threshold' => 50, // Alert if more than 50 connections
        'notification_channels' => ['mail', 'slack'], // How to send alerts
    ],
];