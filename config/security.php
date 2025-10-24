<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enhanced Security Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration provides enhanced security settings for the application
    | including MFA, account lockout, and suspicious activity detection.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Multi-Factor Authentication
    |--------------------------------------------------------------------------
    */

    'mfa' => [
        'enabled' => env('MFA_ENABLED', true),
        'required_for_admin' => env('MFA_REQUIRED_FOR_ADMIN', true),
        'required_for_broker' => env('MFA_REQUIRED_FOR_BROKER', true),
        'backup_codes_count' => env('MFA_BACKUP_CODES_COUNT', 10),
        'token_expiry_minutes' => env('MFA_TOKEN_EXPIRY_MINUTES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Security
    |--------------------------------------------------------------------------
    */

    'login' => [
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 60), // minutes
        'rate_limit' => env('LOGIN_RATE_LIMIT', 5),
        'rate_limit_window' => env('LOGIN_RATE_LIMIT_WINDOW', 15), // minutes
        'track_attempts' => env('LOGIN_ATTEMPT_TRACKING', true),
        'device_fingerprinting' => env('LOGIN_DEVICE_FINGERPRINTING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Security
    |--------------------------------------------------------------------------
    */

    'account' => [
        'suspicious_activity_threshold' => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
        'max_concurrent_sessions' => env('MAX_CONCURRENT_SESSIONS', 3),
        'session_timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 60),
        'force_password_change_days' => env('FORCE_PASSWORD_CHANGE_DAYS', 90),
        'password_history_count' => env('PASSWORD_HISTORY_COUNT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */

    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 12),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
        'check_breach' => env('PASSWORD_CHECK_BREACH', true),
        'max_length' => env('PASSWORD_MAX_LENGTH', 128),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */

    'file_upload' => [
        'virus_scan' => env('FILE_UPLOAD_VIRUS_SCAN', true),
        'content_validation' => env('FILE_UPLOAD_CONTENT_VALIDATION', true),
        'max_size' => env('FILE_UPLOAD_MAX_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */

    'api' => [
        'rate_limit' => env('API_RATE_LIMIT', 100),
        'rate_limit_window' => env('API_RATE_LIMIT_WINDOW', 60), // minutes
        'require_https' => env('API_REQUIRE_HTTPS', true),
        'cors_origins' => env('API_CORS_ORIGINS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */

    'session' => [
        'encrypt' => env('SESSION_ENCRYPT', true),
        'secure_cookie' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
        'fingerprint' => env('SESSION_FINGERPRINT', true),
        'activity_monitoring' => env('SESSION_ACTIVITY_MONITORING', true),
        'concurrent_limit' => env('SESSION_CONCURRENT_LIMIT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' ws: wss:;",
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'security_level' => env('SECURITY_LOG_LEVEL', 'info'),
        'suspicious_activity_level' => env('SUSPICIOUS_ACTIVITY_LOG_LEVEL', 'warning'),
        'failed_login_level' => env('FAILED_LOGIN_LOG_LEVEL', 'warning'),
        'mfa_level' => env('MFA_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'failed_login_email' => env('NOTIFY_FAILED_LOGIN_EMAIL', true),
        'suspicious_activity_email' => env('NOTIFY_SUSPICIOUS_ACTIVITY_EMAIL', true),
        'new_device_email' => env('NOTIFY_NEW_DEVICE_EMAIL', true),
        'admin_email' => env('ADMIN_SECURITY_EMAIL'),
    ],

];