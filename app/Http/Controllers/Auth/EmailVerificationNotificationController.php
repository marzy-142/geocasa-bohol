<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        // Handle authenticated users
        if ($request->user()) {
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }

            $request->user()->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        }

        // Handle guests with pending_user_id in session (just registered)
        $pendingUserId = session('pending_user_id');
        
        if ($pendingUserId) {
            $user = User::find($pendingUserId);
            
            if ($user && !$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
                
                return back()->with('status', 'verification-link-sent');
            }
        }

        // If no user found, redirect to login
        return redirect()->route('login')
            ->with('error', 'Please login to resend the verification email.');
    }
}
