<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings optimized for demo presentation
    |
    */

    'optimize_performance' => true,
    
    'cache_duration' => [
        'stats' => 300, // 5 minutes
        'properties' => 600, // 10 minutes
        'brokers' => 900, // 15 minutes
    ],

    'demo_settings' => [
        'max_properties_per_page' => 12,
        'max_inquiries_per_page' => 10,
        'real_time_updates' => true,
        'show_debug_info' => false,
    ],

    'demo_credentials' => [
        'admin' => [
            'email' => 'admin@geocasabohol.com',
            'password' => 'password',
        ],
        'broker' => [
            'email' => 'maria@geocasabohol.com',
            'password' => 'password',
        ],
    ],
];
