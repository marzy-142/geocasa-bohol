<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBrokerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // IMPROVED: Handle different broker states
        if ($user->role === 'broker') {
            if (!$user->is_approved) {
                // Check application status for better UX
                if ($user->application_status === 'rejected') {
                    return redirect()->route('broker.rejected')
                        ->with('error', 'Your broker application was rejected: ' . $user->rejection_reason);
                }
                
                return redirect()->route('broker.pending-approval')
                    ->with('info', 'Your broker application is still under review.');
            }
        }

        return $next($request);
    }
}
