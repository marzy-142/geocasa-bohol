<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }

    /**
     * Handle email verification for pending users (not yet logged in)
     */
    public function verifyPending(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (!$user || !hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            // User already verified, log them in and redirect
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Email already verified. Welcome!');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            
            // Log the user in after successful verification
            Auth::login($user);
            
            // Clear pending user session
            session()->forget('pending_user_id');
            
            // Redirect based on role
            if ($user->role === 'broker' && !$user->is_approved) {
                return redirect()->route('broker.pending-approval')
                    ->with('success', 'Email verified! Your broker application is being reviewed.');
            }
            
            return redirect()->route('dashboard')
                ->with('success', 'Email verified successfully! Welcome to GeoCasa Bohol.');
        }

        return redirect()->route('verification.notice')
            ->with('error', 'Email verification failed. Please try again.');
    }
}
