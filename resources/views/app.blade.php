<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
            <meta name="user-id" content="{{ auth()->id() }}">
            <meta name="user-role" content="{{ auth()->user()->role }}">
        @endauth

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <style>
            .skip-to-content {
                position: absolute;
                left: -9999px;
                z-index: 999;
                padding: 1rem 1.5rem;
                background-color: #2563eb;
                color: white;
                text-decoration: none;
                border-radius: 0 0 0.5rem 0.5rem;
                font-weight: 600;
            }
            .skip-to-content:focus {
                left: 50%;
                transform: translateX(-50%);
                top: 0;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <a href="#main-content" class="skip-to-content">Skip to main content</a>
        @inertia
    </body>
</html>
