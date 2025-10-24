<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnhancedSecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Check for suspicious activity
            $this->checkSuspiciousActivity($user, $request);

            // Check session security
            $this->checkSessionSecurity($user, $request);

            // Check account status
            $this->checkAccountStatus($user, $request);
        }

        // Add security headers
        $response = $next($request);
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Check for suspicious activity
     */
    private function checkSuspiciousActivity($user, Request $request): void
    {
        // Check for multiple IP addresses in short time
        $recentAttempts = \App\Models\LoginAttempt::where('user_id', $user->id)
            ->where('attempted_at', '>=', now()->subHours(24))
            ->distinct('ip_address')
            ->count();

        if ($recentAttempts > 3) {
            Log::warning('Suspicious activity detected - multiple IPs', [
                'user_id' => $user->id,
                'ip_addresses_count' => $recentAttempts,
                'current_ip' => $request->ip(),
            ]);
        }

        // Check for rapid location changes
        $this->checkLocationChanges($user, $request);
    }

    /**
     * Check for rapid location changes
     */
    private function checkLocationChanges($user, Request $request): void
    {
        $recentAttempts = \App\Models\LoginAttempt::where('user_id', $user->id)
            ->where('attempted_at', '>=', now()->subHours(1))
            ->orderBy('attempted_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentAttempts->count() > 2) {
            $uniqueIps = $recentAttempts->pluck('ip_address')->unique()->count();
            
            if ($uniqueIps > 2) {
                Log::alert('Potential account compromise - rapid location changes', [
                    'user_id' => $user->id,
                    'unique_ips_in_hour' => $uniqueIps,
                    'current_ip' => $request->ip(),
                ]);
            }
        }
    }

    /**
     * Check session security
     */
    private function checkSessionSecurity($user, Request $request): void
    {
        // Check session age
        $sessionAge = now()->diffInMinutes($request->session()->get('created_at', now()));
        
        if ($sessionAge > 120) { // 2 hours
            Log::info('Long session detected', [
                'user_id' => $user->id,
                'session_age_minutes' => $sessionAge,
            ]);
        }

        // Check for concurrent sessions
        $this->checkConcurrentSessions($user, $request);
    }

    /**
     * Check for concurrent sessions
     */
    private function checkConcurrentSessions($user, Request $request): void
    {
        // This would require session tracking implementation
        // For now, we'll log the check
        Log::debug('Concurrent session check', [
            'user_id' => $user->id,
            'current_session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Check account status
     */
    private function checkAccountStatus($user, Request $request): void
    {
        // Check if account is suspended
        if ($user->is_suspended) {
            Log::warning('Suspended user attempted access', [
                'user_id' => $user->id,
                'suspension_reason' => $user->suspension_reason,
                'suspended_until' => $user->suspended_until,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            abort(403, 'Your account has been suspended.');
        }

        // Check if broker approval is required
        if ($user->role === 'broker' && !$user->is_approved && !$request->routeIs('broker.pending-approval')) {
            Log::info('Unapproved broker attempted access', [
                'user_id' => $user->id,
                'application_status' => $user->application_status,
            ]);
        }
    }

    /**
     * Add security headers
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' ws: wss:;",
        ];

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
    }
}



