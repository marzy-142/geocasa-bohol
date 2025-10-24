<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        
        // Web middleware
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\LogRequests::class,
        ]);

        // API middleware with CORS and rate limiting
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'web.role' => \App\Http\Middleware\EnsureUserRole::class,
            'broker.approved' => \App\Http\Middleware\EnsureBrokerApproved::class,
            'log.requests' => \App\Http\Middleware\LogRequests::class,
            'file.security' => \App\Http\Middleware\FileSecurityMiddleware::class,
            'file.rate.limit' => \App\Http\Middleware\FileUploadRateLimitMiddleware::class,
            'api.rate.limit' => \App\Http\Middleware\ApiRateLimitMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Configure throttling for different route groups
        $middleware->throttleApi('60,1'); // 60 requests per minute for API
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
