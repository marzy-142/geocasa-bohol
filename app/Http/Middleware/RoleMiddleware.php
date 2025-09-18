<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): BaseResponse
    {
        // Check if user is authenticated
        if (!$request->user()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }
            return redirect()->route('login');
        }

        // Check if user has the required role
        if ($request->user()->role !== $role) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions. Required role: ' . $role
                ], Response::HTTP_FORBIDDEN);
            }
            
            // For web requests, redirect to dashboard with error message
            return redirect()->route('dashboard')->with('error', 'Insufficient permissions. Required role: ' . $role);
        }

        return $next($request);
    }
}