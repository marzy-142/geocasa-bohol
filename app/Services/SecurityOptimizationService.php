<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecurityOptimizationService
{
    /**
     * Get security statistics
     */
    public function getSecurityStats(): array
    {
        return [
            'failed_logins' => $this->getFailedLoginCount(),
            'blocked_ips' => $this->getBlockedIpCount(),
            'suspicious_activities' => $this->getSuspiciousActivityCount(),
            'password_resets' => $this->getPasswordResetCount(),
            'security_events' => $this->getSecurityEventCount(),
        ];
    }

    /**
     * Get failed login count
     */
    private function getFailedLoginCount(): int
    {
        return RateLimiter::attempts('login:' . request()->ip()) ?? 0;
    }

    /**
     * Get blocked IP count
     */
    private function getBlockedIpCount(): int
    {
        $blockedIps = Cache::get('blocked_ips', []);
        return count($blockedIps);
    }

    /**
     * Get suspicious activity count
     */
    private function getSuspiciousActivityCount(): int
    {
        return Cache::get('suspicious_activities', 0);
    }

    /**
     * Get password reset count
     */
    private function getPasswordResetCount(): int
    {
        return Cache::get('password_resets', 0);
    }

    /**
     * Get security event count
     */
    private function getSecurityEventCount(): int
    {
        return Cache::get('security_events', 0);
    }

    /**
     * Optimize security settings
     */
    public function optimizeSecuritySettings(): array
    {
        $optimizations = [];
        
        // Check password requirements
        $optimizations['password'] = $this->optimizePasswordRequirements();
        
        // Check session security
        $optimizations['session'] = $this->optimizeSessionSecurity();
        
        // Check CSRF protection
        $optimizations['csrf'] = $this->optimizeCsrfProtection();
        
        // Check rate limiting
        $optimizations['rate_limiting'] = $this->optimizeRateLimiting();
        
        // Check file upload security
        $optimizations['file_upload'] = $this->optimizeFileUploadSecurity();
        
        return $optimizations;
    }

    /**
     * Optimize password requirements
     */
    private function optimizePasswordRequirements(): array
    {
        $optimizations = [];
        
        // Check minimum password length
        $minLength = config('auth.password.min_length', 8);
        if ($minLength < 12) {
            $optimizations[] = 'Consider increasing minimum password length to 12 characters';
        }
        
        // Check password complexity
        if (!config('auth.password.require_uppercase')) {
            $optimizations[] = 'Consider requiring uppercase letters in passwords';
        }
        
        if (!config('auth.password.require_lowercase')) {
            $optimizations[] = 'Consider requiring lowercase letters in passwords';
        }
        
        if (!config('auth.password.require_numbers')) {
            $optimizations[] = 'Consider requiring numbers in passwords';
        }
        
        if (!config('auth.password.require_symbols')) {
            $optimizations[] = 'Consider requiring symbols in passwords';
        }
        
        return $optimizations;
    }

    /**
     * Optimize session security
     */
    private function optimizeSessionSecurity(): array
    {
        $optimizations = [];
        
        // Check session encryption
        if (!config('session.encrypt')) {
            $optimizations[] = 'Enable session encryption for better security';
        }
        
        // Check session secure flag
        if (!config('session.secure') && app()->environment('production')) {
            $optimizations[] = 'Enable secure flag for sessions in production';
        }
        
        // Check session HTTP only flag
        if (!config('session.http_only')) {
            $optimizations[] = 'Enable HTTP only flag for sessions';
        }
        
        // Check session same site
        $sameSite = config('session.same_site');
        if ($sameSite !== 'strict' && app()->environment('production')) {
            $optimizations[] = 'Consider setting session same site to strict';
        }
        
        return $optimizations;
    }

    /**
     * Optimize CSRF protection
     */
    private function optimizeCsrfProtection(): array
    {
        $optimizations = [];
        
        // Check CSRF token lifetime
        $tokenLifetime = config('session.lifetime', 120);
        if ($tokenLifetime > 1440) { // 24 hours
            $optimizations[] = 'Consider reducing CSRF token lifetime for better security';
        }
        
        // Check CSRF token regeneration
        if (!config('session.regenerate_on_login')) {
            $optimizations[] = 'Consider regenerating CSRF token on login';
        }
        
        return $optimizations;
    }

    /**
     * Optimize rate limiting
     */
    private function optimizeRateLimiting(): array
    {
        $optimizations = [];
        
        // Check login rate limiting
        $loginLimit = config('auth.rate_limit.login', 5);
        if ($loginLimit > 10) {
            $optimizations[] = 'Consider reducing login rate limit for better security';
        }
        
        // Check password reset rate limiting
        $passwordResetLimit = config('auth.rate_limit.password_reset', 3);
        if ($passwordResetLimit > 5) {
            $optimizations[] = 'Consider reducing password reset rate limit';
        }
        
        return $optimizations;
    }

    /**
     * Optimize file upload security
     */
    private function optimizeFileUploadSecurity(): array
    {
        $optimizations = [];
        
        // Check file upload size limits
        $maxFileSize = config('filesystems.max_file_size', 5120); // 5MB
        if ($maxFileSize > 10240) { // 10MB
            $optimizations[] = 'Consider reducing maximum file upload size';
        }
        
        // Check allowed file types
        $allowedTypes = config('filesystems.allowed_types', []);
        if (in_array('php', $allowedTypes) || in_array('exe', $allowedTypes)) {
            $optimizations[] = 'Remove dangerous file types from allowed uploads';
        }
        
        return $optimizations;
    }

    /**
     * Monitor suspicious activity
     */
    public function monitorSuspiciousActivity(Request $request): void
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $path = $request->path();
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            'admin' => '/admin/',
            'login' => '/login/',
            'password' => '/password/',
            'sql' => '/sql/',
            'script' => '/script/',
            'eval' => '/eval/',
            'exec' => '/exec/',
            'system' => '/system/',
        ];
        
        foreach ($suspiciousPatterns as $pattern => $regex) {
            if (preg_match($regex, $path)) {
                $this->logSuspiciousActivity($ip, $userAgent, $path, $pattern);
            }
        }
        
        // Check for rapid requests
        $this->checkRapidRequests($ip);
        
        // Check for unusual user agents
        $this->checkUnusualUserAgent($userAgent);
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(string $ip, string $userAgent, string $path, string $pattern): void
    {
        $activity = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'path' => $path,
            'pattern' => $pattern,
            'timestamp' => now(),
        ];
        
        Log::warning('Suspicious activity detected', $activity);
        
        // Increment suspicious activity counter
        $count = Cache::get('suspicious_activities', 0);
        Cache::put('suspicious_activities', $count + 1, 3600);
        
        // Check if IP should be blocked
        $this->checkIpBlocking($ip);
    }

    /**
     * Check rapid requests
     */
    private function checkRapidRequests(string $ip): void
    {
        $key = 'rapid_requests:' . $ip;
        $count = RateLimiter::attempts($key);
        
        if ($count > 100) { // More than 100 requests per minute
            $this->logSuspiciousActivity($ip, '', '', 'rapid_requests');
        }
    }

    /**
     * Check unusual user agent
     */
    private function checkUnusualUserAgent(string $userAgent): void
    {
        $suspiciousUserAgents = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'curl',
            'wget',
            'python',
            'php',
        ];
        
        foreach ($suspiciousUserAgents as $suspicious) {
            if (stripos($userAgent, $suspicious) !== false) {
                $this->logSuspiciousActivity(request()->ip(), $userAgent, request()->path(), 'suspicious_user_agent');
                break;
            }
        }
    }

    /**
     * Check IP blocking
     */
    private function checkIpBlocking(string $ip): void
    {
        $suspiciousCount = Cache::get('suspicious_activities', 0);
        
        if ($suspiciousCount > 10) {
            $this->blockIp($ip);
        }
    }

    /**
     * Block IP address
     */
    public function blockIp(string $ip): void
    {
        $blockedIps = Cache::get('blocked_ips', []);
        $blockedIps[] = $ip;
        Cache::put('blocked_ips', $blockedIps, 86400); // 24 hours
        
        Log::warning('IP address blocked', ['ip' => $ip]);
    }

    /**
     * Unblock IP address
     */
    public function unblockIp(string $ip): void
    {
        $blockedIps = Cache::get('blocked_ips', []);
        $blockedIps = array_diff($blockedIps, [$ip]);
        Cache::put('blocked_ips', $blockedIps, 86400);
        
        Log::info('IP address unblocked', ['ip' => $ip]);
    }

    /**
     * Check if IP is blocked
     */
    public function isIpBlocked(string $ip): bool
    {
        $blockedIps = Cache::get('blocked_ips', []);
        return in_array($ip, $blockedIps);
    }

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations(): array
    {
        $stats = $this->getSecurityStats();
        $optimizations = $this->optimizeSecuritySettings();
        
        $recommendations = [];
        
        // High failed login count
        if ($stats['failed_logins'] > 50) {
            $recommendations[] = 'High number of failed login attempts. Consider implementing additional security measures';
        }
        
        // Many blocked IPs
        if ($stats['blocked_ips'] > 100) {
            $recommendations[] = 'Many IPs are blocked. Consider reviewing blocking criteria';
        }
        
        // High suspicious activity
        if ($stats['suspicious_activities'] > 20) {
            $recommendations[] = 'High suspicious activity detected. Consider investigating';
        }
        
        // Add specific optimizations
        foreach ($optimizations as $category => $categoryOptimizations) {
            if (!empty($categoryOptimizations)) {
                $recommendations[$category] = $categoryOptimizations;
            }
        }
        
        return $recommendations;
    }

    /**
     * Generate secure random string
     */
    public function generateSecureRandomString(int $length = 32): string
    {
        return Str::random($length);
    }

    /**
     * Hash sensitive data
     */
    public function hashSensitiveData(string $data): string
    {
        return Hash::make($data);
    }

    /**
     * Verify sensitive data
     */
    public function verifySensitiveData(string $data, string $hash): bool
    {
        return Hash::check($data, $hash);
    }

    /**
     * Get security performance metrics
     */
    public function getSecurityPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test security operations
        $this->generateSecureRandomString();
        $this->hashSensitiveData('test_data');
        $this->isIpBlocked('127.0.0.1');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'security_operations_time' => $executionTime,
            'security_operations_time_ms' => round($executionTime * 1000, 2),
            'blocked_ips_count' => $this->getBlockedIpCount(),
            'suspicious_activities_count' => $this->getSuspiciousActivityCount(),
        ];
    }
}

