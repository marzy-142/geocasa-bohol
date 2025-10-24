<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the backup settings for your application.
    |
    */

    /*
     * The name of this application. You can use this name to monitor
     * the backups.
     */
    'name' => env('APP_NAME', 'GeoCasa Bohol'),

    /*
     * The disk where backups will be stored.
     */
    'disk' => env('BACKUP_DISK', 'backup'),

    /*
     * Maximum number of backups to keep.
     */
    'max_backups' => env('BACKUP_MAX_BACKUPS', 7),

    /*
     * Backup schedule configuration
     */
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', true),
        'frequency' => env('BACKUP_FREQUENCY', 'daily'), // daily, weekly, monthly
        'time' => env('BACKUP_TIME', '02:00'), // Time to run backup (24h format)
    ],

    /*
     * Database backup configuration
     */
    'database' => [
        'enabled' => env('BACKUP_DATABASE_ENABLED', true),
        'connections' => [
            config('database.default'),
        ],
        'options' => [
            'single_transaction' => true,
            'routines' => true,
            'triggers' => true,
            'add_drop_table' => true,
        ],
    ],

    /*
     * File backup configuration
     */
    'files' => [
        'enabled' => env('BACKUP_FILES_ENABLED', true),
        'include' => [
            'storage/app',
            'public/uploads',
            '.env',
        ],
        'exclude' => [
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'node_modules',
            'vendor',
            '.git',
        ],
    ],

    /*
     * Compression settings
     */
    'compression' => [
        'enabled' => env('BACKUP_COMPRESSION_ENABLED', true),
        'method' => env('BACKUP_COMPRESSION_METHOD', 'gzip'), // gzip, zip
    ],

    /*
     * Notification settings
     */
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'channels' => [
            'mail' => [
                'enabled' => env('BACKUP_MAIL_NOTIFICATIONS', true),
                'to' => env('BACKUP_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
            ],
            'slack' => [
                'enabled' => env('BACKUP_SLACK_NOTIFICATIONS', false),
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL'),
            ],
        ],
        'events' => [
            'backup_successful' => true,
            'backup_failed' => true,
            'cleanup_successful' => false,
            'cleanup_failed' => true,
        ],
    ],

    /*
     * Health check settings
     */
    'health_checks' => [
        'enabled' => env('BACKUP_HEALTH_CHECKS_ENABLED', true),
        'max_age_days' => env('BACKUP_MAX_AGE_DAYS', 2),
        'min_size_mb' => env('BACKUP_MIN_SIZE_MB', 1),
    ],

    /*
     * Monitoring settings
     */
    'monitoring' => [
        'enabled' => env('BACKUP_MONITORING_ENABLED', true),
        'log_level' => env('BACKUP_LOG_LEVEL', 'info'),
        'metrics' => [
            'backup_duration' => true,
            'backup_size' => true,
            'disk_usage' => true,
        ],
    ],

];