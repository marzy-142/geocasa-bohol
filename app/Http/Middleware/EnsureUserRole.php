<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        // FIXED: Only check approval for broker routes that require approval
        // Don't redirect if already on pending-approval route
        if ($user->role === 'broker' && !$user->is_approved) {
            if (!$request->routeIs('broker.pending-approval')) {
                return redirect()->route('broker.pending-approval');
            }
        }

        return $next($request);
    }
}
