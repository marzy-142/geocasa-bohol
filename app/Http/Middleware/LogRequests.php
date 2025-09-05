<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Log incoming request
        $this->logRequest($request);
        
        $response = $next($request);
        
        // Log response
        $this->logResponse($request, $response, $startTime);
        
        return $response;
    }
    
    /**
     * Log incoming request details
     */
    protected function logRequest(Request $request): void
    {
        // Skip logging for health checks and static assets
        if ($this->shouldSkipLogging($request)) {
            return;
        }
        
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
        ];
        
        // Add request body for non-GET requests (excluding sensitive data)
        if (!$request->isMethod('GET')) {
            $input = $request->except([
                'password',
                'password_confirmation',
                'current_password',
                'prc_id',
                'business_permit',
                '_token',
            ]);
            
            if (!empty($input)) {
                $logData['input'] = $input;
            }
        }
        
        Log::info('Incoming Request', $logData);
    }
    
    /**
     * Log response details
     */
    protected function logResponse(Request $request, Response $response, float $startTime): void
    {
        // Skip logging for health checks and static assets
        if ($this->shouldSkipLogging($request)) {
            return;
        }
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
            'user_id' => auth()->id(),
        ];
        
        // Log level based on status code
        $logLevel = $this->getLogLevel($response->getStatusCode());
        
        // Add error details for error responses
        if ($response->getStatusCode() >= 400) {
            $logData['response_size'] = strlen($response->getContent());
            
            // For JSON responses, try to decode and log error message
            if ($response->headers->get('Content-Type') === 'application/json') {
                $content = json_decode($response->getContent(), true);
                if (isset($content['message'])) {
                    $logData['error_message'] = $content['message'];
                }
            }
        }
        
        Log::log($logLevel, 'Request Completed', $logData);
        
        // Log slow requests
        if ($duration > 1000) { // Requests taking more than 1 second
            Log::warning('Slow Request Detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'duration_ms' => $duration,
                'user_id' => auth()->id(),
            ]);
        }
    }
    
    /**
     * Determine if request logging should be skipped
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $skipPaths = [
            '/health',
            '/ping',
            '/favicon.ico',
            '/robots.txt',
        ];
        
        $path = $request->path();
        
        // Skip static assets
        if (str_starts_with($path, 'build/') || 
            str_ends_with($path, '.css') || 
            str_ends_with($path, '.js') || 
            str_ends_with($path, '.png') || 
            str_ends_with($path, '.jpg') || 
            str_ends_with($path, '.jpeg') || 
            str_ends_with($path, '.gif') || 
            str_ends_with($path, '.svg')) {
            return true;
        }
        
        return in_array('/' . $path, $skipPaths);
    }
    
    /**
     * Get appropriate log level based on HTTP status code
     */
    protected function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }
        
        if ($statusCode >= 400) {
            return 'warning';
        }
        
        return 'info';
    }
    
    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}