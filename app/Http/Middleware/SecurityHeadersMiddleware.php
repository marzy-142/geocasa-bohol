<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if security headers are enabled
        if (!config('security.headers.enabled', true)) {
            return $response;
        }

        // Content Security Policy
        if (config('security.headers.csp.enabled', true)) {
            $this->setContentSecurityPolicy($response, $request);
        }

        // HTTP Strict Transport Security (HSTS)
        if (config('security.headers.hsts.enabled', true) && $request->isSecure()) {
            $this->setHstsHeader($response);
        }

        // Other security headers
        $this->setSecurityHeaders($response);

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        // Cache control for sensitive pages
        if ($this->isSensitiveRoute($request)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Log security headers if enabled
        if (config('security.logging.log_security_headers', false)) {
            Log::channel(config('security.logging.log_channel', 'daily'))
                ->info('Security headers applied', [
                    'route' => $request->route()?->getName(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
        }

        return $response;
    }

    /**
     * Set Content Security Policy header.
     */
    private function setContentSecurityPolicy(Response $response, Request $request): void
    {
        $directives = config('security.csp_directives', []);
        $cspParts = [];

        foreach ($directives as $directive => $sources) {
            if ($directive === 'upgrade-insecure-requests' && $sources === true) {
                $cspParts[] = 'upgrade-insecure-requests';
            } elseif (is_array($sources) && !empty($sources)) {
                $cspParts[] = $directive . ' ' . implode(' ', $sources);
            }
        }

        $csp = implode('; ', $cspParts);
        
        $headerName = config('security.headers.csp.report_only', false) 
            ? 'Content-Security-Policy-Report-Only' 
            : 'Content-Security-Policy';
            
        $response->headers->set($headerName, $csp);
    }

    /**
     * Set HSTS header.
     */
    private function setHstsHeader(Response $response): void
    {
        $maxAge = config('security.headers.hsts.max_age', 31536000);
        $includeSubdomains = config('security.headers.hsts.include_subdomains', true);
        $preload = config('security.headers.hsts.preload', true);

        $hsts = "max-age={$maxAge}";
        if ($includeSubdomains) {
            $hsts .= '; includeSubDomains';
        }
        if ($preload) {
            $hsts .= '; preload';
        }

        $response->headers->set('Strict-Transport-Security', $hsts);
    }

    /**
     * Set other security headers.
     */
    private function setSecurityHeaders(Response $response): void
    {
        // X-Frame-Options
        $response->headers->set('X-Frame-Options', config('security.headers.frame_options', 'DENY'));

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', config('security.headers.content_type_options', 'nosniff'));

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', config('security.headers.xss_protection', '1; mode=block'));

        // Referrer Policy
        $response->headers->set('Referrer-Policy', config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));

        // Permissions Policy
        $this->setPermissionsPolicy($response);
    }

    /**
     * Set Permissions Policy header.
     */
    private function setPermissionsPolicy(Response $response): void
    {
        $policies = config('security.permissions_policy', []);
        $policyParts = [];

        foreach ($policies as $feature => $allowlist) {
            if (empty($allowlist)) {
                $policyParts[] = "{$feature}=()";
            } else {
                $sources = implode(' ', array_map(function ($source) {
                    return $source === 'self' ? 'self' : "\"$source\"";
                }, $allowlist));
                $policyParts[] = "{$feature}=({$sources})";
            }
        }

        if (!empty($policyParts)) {
            $response->headers->set('Permissions-Policy', implode(', ', $policyParts));
        }
    }

    /**
     * Check if the current route is sensitive and should not be cached.
     */
    private function isSensitiveRoute(Request $request): bool
    {
        $sensitiveRoutes = config('security.sensitive_routes', []);
        $currentRoute = $request->route()?->getName();
        
        if (!$currentRoute) {
            return false;
        }

        foreach ($sensitiveRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        return false;
    }
}