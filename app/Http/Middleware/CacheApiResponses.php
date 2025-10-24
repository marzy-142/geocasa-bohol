<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class CacheApiResponses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Don't cache authenticated requests with sensitive data
        if (Auth::check() && $this->isSensitiveEndpoint($request)) {
            return $next($request);
        }

        // Generate cache key based on request
        $cacheKey = $this->generateCacheKey($request);
        
        // Check if response is cached
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Process request
        $response = $next($request);

        // Cache successful JSON responses
        if ($response->getStatusCode() === 200 && $response->headers->get('content-type') === 'application/json') {
            $cacheDuration = $this->getCacheDuration($request);
            Cache::put($cacheKey, $response->getData(true), $cacheDuration);
        }

        return $response;
    }

    /**
     * Generate cache key for the request
     */
    private function generateCacheKey(Request $request): string
    {
        $key = 'api_cache:' . $request->path();
        
        // Include query parameters
        if ($request->query()) {
            $key .= ':' . md5(serialize($request->query()));
        }
        
        // Include user ID if authenticated
        if (Auth::check()) {
            $key .= ':user:' . Auth::id();
        }
        
        return $key;
    }

    /**
     * Check if endpoint contains sensitive data
     */
    private function isSensitiveEndpoint(Request $request): bool
    {
        $sensitivePaths = [
            'admin/analytics',
            'admin/statistics',
            'broker/performance',
            'user/profile',
            'transactions/personal',
            'inquiries/personal',
        ];

        foreach ($sensitivePaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cache duration based on endpoint
     */
    private function getCacheDuration(Request $request): int
    {
        $path = $request->path();
        
        // Different cache durations for different endpoints
        if (str_contains($path, 'properties')) {
            return 900; // 15 minutes for properties
        }
        
        if (str_contains($path, 'brokers')) {
            return 600; // 10 minutes for brokers
        }
        
        if (str_contains($path, 'statistics')) {
            return 300; // 5 minutes for statistics
        }
        
        if (str_contains($path, 'analytics')) {
            return 180; // 3 minutes for analytics
        }
        
        // Default cache duration
        return 300; // 5 minutes
    }
}

