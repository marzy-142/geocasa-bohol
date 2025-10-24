<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $maxAttempts  Maximum number of attempts allowed
     * @param  int  $decayMinutes  Number of minutes until attempts reset
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Check if rate limiting is enabled
        if (!config('security.rate_limiting.enabled', true)) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($request, $key);
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = auth()->id();
        $ip = $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        // Use user ID if authenticated, otherwise use IP
        $identifier = $userId ? "user:{$userId}" : "ip:{$ip}";
        
        return "api_rate_limit:{$identifier}:{$route}";
    }

    /**
     * Determine if the given key has been "accessed" too many times
     */
    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return Cache::get($key, 0) >= $maxAttempts;
    }

    /**
     * Increment the counter for a given key for a given decay time
     */
    protected function hit(string $key, int $decaySeconds): int
    {
        $current = Cache::get($key, 0);
        $new = $current + 1;
        
        Cache::put($key, $new, now()->addSeconds($decaySeconds));
        
        return $new;
    }

    /**
     * Calculate the number of remaining attempts
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - Cache::get($key, 0));
    }

    /**
     * Create a 'too many attempts' response
     */
    protected function buildResponse(string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);
        
        $response = response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'error' => 'rate_limit_exceeded',
            'retry_after' => $retryAfter
        ], 429);

        return $this->addHeaders($response, $maxAttempts, 0, $retryAfter);
    }

    /**
     * Add rate limit headers to the response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, ?int $retryAfter = null): Response
    {
        // Add rate limit headers if enabled
        if (config('security.rate_limiting.headers.include_headers', true)) {
            $response->headers->add([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => $remainingAttempts,
            ]);
        }

        if ($retryAfter !== null) {
            if (config('security.rate_limiting.headers.retry_after_header', true)) {
                $response->headers->set('Retry-After', $retryAfter);
            }
            if (config('security.rate_limiting.headers.include_headers', true)) {
                $response->headers->set('X-RateLimit-Reset', now()->addSeconds($retryAfter)->timestamp);
            }
        }

        return $response;
    }

    /**
     * Get the number of seconds until the next retry
     */
    protected function getTimeUntilNextRetry(string $key): int
    {
        // Get the TTL of the cache key
        $ttl = Cache::getStore()->getRedis()->ttl($key);
        
        return max(1, $ttl > 0 ? $ttl : 60);
    }

    /**
     * Log rate limit exceeded event
     */
    protected function logRateLimitExceeded(Request $request, string $key): void
    {
        // Log rate limit exceeded if enabled
        if (config('security.logging.log_rate_limits', true)) {
            Log::channel(config('security.logging.log_channel', 'daily'))
                ->warning('API rate limit exceeded', [
                    'key' => $key,
                    'ip' => $request->ip(),
                    'user_id' => auth()->id(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->route()?->getName(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'timestamp' => now()->toISOString()
                ]);
        }
    }
}