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
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if user has the required role
        if ($request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required role: ' . $role
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}