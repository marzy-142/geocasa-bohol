<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security headers that will be applied to all responses.
    | These headers help protect against various security vulnerabilities.
    |
    */

    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
        
        'csp' => [
            'enabled' => env('CSP_ENABLED', true),
            'report_only' => env('CSP_REPORT_ONLY', false),
            'report_uri' => env('CSP_REPORT_URI', null),
        ],
        
        'hsts' => [
            'enabled' => env('HSTS_ENABLED', true),
            'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
            'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('HSTS_PRELOAD', true),
        ],
        
        'frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different types of requests.
    | Values are in format 'max_attempts,decay_minutes'
    |
    */

    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        
        'limits' => [
            'auth' => env('RATE_LIMIT_AUTH', '5,1'),           // Authentication endpoints
            'api_public' => env('RATE_LIMIT_API_PUBLIC', '30,1'), // Public API endpoints
            'api_user' => env('RATE_LIMIT_API_USER', '60,1'),     // Authenticated user endpoints
            'api_broker' => env('RATE_LIMIT_API_BROKER', '100,1'), // Broker endpoints
            'api_admin' => env('RATE_LIMIT_API_ADMIN', '200,1'),   // Admin endpoints
            'file_upload' => env('RATE_LIMIT_FILE_UPLOAD', '10,1'), // File upload endpoints
        ],
        
        'headers' => [
            'include_headers' => env('RATE_LIMIT_INCLUDE_HEADERS', true),
            'retry_after_header' => env('RATE_LIMIT_RETRY_AFTER_HEADER', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP) Configuration
    |--------------------------------------------------------------------------
    |
    | Define Content Security Policy directives to prevent XSS attacks
    | and other code injection attacks.
    |
    */

    'csp_directives' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'",
            "'unsafe-inline'", // Required for some frameworks, consider removing in production
            "'unsafe-eval'",   // Required for some frameworks, consider removing in production
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            // Add local development server for Vite
            'http://localhost:5173',
            'http://[::1]:5173',
            'http://127.0.0.1:5173',
        ],
        'style-src' => [
            "'self'",
            "'unsafe-inline'", // Required for inline styles
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            // Add fonts.bunny.net for font loading
            'https://fonts.bunny.net',
        ],
        'font-src' => [
            "'self'",
            'https://fonts.gstatic.com',
            // Add fonts.bunny.net for font files
            'https://fonts.bunny.net',
        ],
        'img-src' => [
            "'self'",
            'data:',
            'https:',
            'blob:',
        ],
        'media-src' => ["'self'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'none'"],
        'upgrade-insecure-requests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Control which browser features can be used by the application.
    |
    */

    'permissions_policy' => [
        'camera' => [],
        'microphone' => [],
        'geolocation' => ['self'],
        'payment' => [],
        'usb' => [],
        'magnetometer' => [],
        'gyroscope' => [],
        'accelerometer' => [],
        'ambient-light-sensor' => [],
        'autoplay' => ['self'],
        'encrypted-media' => ['self'],
        'fullscreen' => ['self'],
        'picture-in-picture' => ['self'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Define route patterns that should have strict caching policies
    | and additional security measures.
    |
    */

    'sensitive_routes' => [
        'login',
        'register',
        'password.*',
        'admin.*',
        'broker.*',
        'client.*',
        'dashboard',
        'profile.*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for security-related events.
    |
    */

    'logging' => [
        'enabled' => env('SECURITY_LOGGING_ENABLED', true),
        'log_channel' => env('SECURITY_LOG_CHANNEL', 'daily'),
        'log_rate_limits' => env('LOG_RATE_LIMITS', true),
        'log_security_headers' => env('LOG_SECURITY_HEADERS', false),
    ],

];