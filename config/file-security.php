<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the file security service
    | including virus scanning, file validation, and security policies.
    |
    */

    'virus_scanning' => [
        'enabled' => env('VIRUS_SCANNING_ENABLED', false),
        'service' => env('VIRUS_SCANNING_SERVICE', 'clamav'), // clamav, virustotal, etc.
        'api_key' => env('VIRUS_SCANNING_API_KEY'),
        'timeout' => env('VIRUS_SCANNING_TIMEOUT', 30),
        'quarantine_path' => storage_path('app/quarantine'),
    ],

    'file_validation' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10485760), // 10MB default
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'dangerous_extensions' => [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'php', 'asp', 'aspx', 'jsp', 'py', 'rb', 'pl', 'sh', 'ps1',
            'msi', 'deb', 'rpm', 'dmg', 'pkg', 'app', 'zip', 'rar', '7z',
        ],
        'suspicious_signatures' => [
            'MZ', // PE executable
            'PK', // ZIP archive
            '7z', // 7-Zip archive
            'Rar!', // RAR archive
            '<?php', // PHP code
            '<script', // JavaScript
            'eval(', // Potentially malicious code
        ],
    ],

    'storage' => [
        'secure_permissions' => 0644,
        'directory_permissions' => 0755,
        'filename_sanitization' => true,
        'generate_unique_names' => true,
    ],

    'logging' => [
        'enabled' => env('FILE_SECURITY_LOGGING', true),
        'log_channel' => env('FILE_SECURITY_LOG_CHANNEL', 'daily'),
        'log_suspicious_files' => true,
        'log_virus_scans' => true,
    ],

    'content_validation' => [
        'validate_magic_bytes' => true,
        'validate_file_headers' => true,
        'check_embedded_content' => true,
    ],

    'rate_limiting' => [
        'enabled' => env('FILE_UPLOAD_RATE_LIMITING', true),
        'max_uploads_per_minute' => env('MAX_UPLOADS_PER_MINUTE', 10),
        'max_uploads_per_hour' => env('MAX_UPLOADS_PER_HOUR', 100),
    ],
];