<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FileUploadRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('file-security.rate_limiting.enabled', true)) {
            return $next($request);
        }

        // Check if request contains file uploads
        if (!$this->hasFileUploads($request)) {
            return $next($request);
        }

        $clientIp = $request->ip();
        $userId = auth()->id() ?? 'anonymous';
        $identifier = "file_upload_rate_limit:{$clientIp}:{$userId}";

        // Check rate limits
        if (!$this->checkRateLimit($identifier, $request)) {
            Log::warning('File upload rate limit exceeded', [
                'ip' => $clientIp,
                'user_id' => $userId,
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
            ]);

            return response()->json([
                'message' => 'Too many file upload attempts. Please try again later.',
                'error' => 'rate_limit_exceeded'
            ], 429);
        }

        // Increment counters
        $this->incrementCounters($identifier);

        return $next($request);
    }

    /**
     * Check if the request contains file uploads
     */
    private function hasFileUploads(Request $request): bool
    {
        return $request->hasFile('*') || 
               collect($request->allFiles())->flatten()->isNotEmpty();
    }

    /**
     * Check rate limits for the given identifier
     */
    private function checkRateLimit(string $identifier, Request $request): bool
    {
        $maxPerMinute = config('file-security.rate_limiting.max_uploads_per_minute', 10);
        $maxPerHour = config('file-security.rate_limiting.max_uploads_per_hour', 100);

        $minuteKey = "{$identifier}:minute:" . now()->format('Y-m-d-H-i');
        $hourKey = "{$identifier}:hour:" . now()->format('Y-m-d-H');

        $minuteCount = Cache::get($minuteKey, 0);
        $hourCount = Cache::get($hourKey, 0);

        // Check minute limit
        if ($minuteCount >= $maxPerMinute) {
            return false;
        }

        // Check hour limit
        if ($hourCount >= $maxPerHour) {
            return false;
        }

        return true;
    }

    /**
     * Increment rate limit counters
     */
    private function incrementCounters(string $identifier): void
    {
        $minuteKey = "{$identifier}:minute:" . now()->format('Y-m-d-H-i');
        $hourKey = "{$identifier}:hour:" . now()->format('Y-m-d-H');

        // Increment minute counter (expires after 1 minute)
        Cache::put($minuteKey, Cache::get($minuteKey, 0) + 1, now()->addMinute());

        // Increment hour counter (expires after 1 hour)
        Cache::put($hourKey, Cache::get($hourKey, 0) + 1, now()->addHour());
    }
}