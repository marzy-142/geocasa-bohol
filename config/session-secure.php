<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Enhanced Session Security Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration provides enhanced security settings for session
    | management in production environments.
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => env('SESSION_LIFETIME', 60), // Reduced to 1 hour

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', true), // Close on browser close

    'encrypt' => env('SESSION_ENCRYPT', true), // Always encrypt sessions

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    'path' => '/',

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE', true), // Force HTTPS in production

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'strict'), // Strict CSRF protection

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | Additional Security Features
    |--------------------------------------------------------------------------
    */

    // Session fingerprinting for additional security
    'fingerprint' => [
        'enabled' => env('SESSION_FINGERPRINT_ENABLED', true),
        'fields' => ['ip_address', 'user_agent'], // Fields to include in fingerprint
    ],

    // Concurrent session limits
    'concurrent_sessions' => [
        'enabled' => env('SESSION_CONCURRENT_LIMIT_ENABLED', true),
        'max_sessions' => env('SESSION_MAX_CONCURRENT', 3),
        'terminate_oldest' => true, // Terminate oldest session when limit exceeded
    ],

    // Session activity monitoring
    'activity_monitoring' => [
        'enabled' => env('SESSION_ACTIVITY_MONITORING', true),
        'timeout_minutes' => env('SESSION_ACTIVITY_TIMEOUT', 15), // Auto-logout after inactivity
        'warn_minutes' => env('SESSION_ACTIVITY_WARN', 10), // Warn user before timeout
    ],

    // Suspicious activity detection
    'suspicious_activity' => [
        'enabled' => env('SESSION_SUSPICIOUS_ACTIVITY_DETECTION', true),
        'max_ip_changes' => env('SESSION_MAX_IP_CHANGES', 3), // Max IP changes per session
        'auto_logout' => true, // Auto-logout on suspicious activity
    ],

];



