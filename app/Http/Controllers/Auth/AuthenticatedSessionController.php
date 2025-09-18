<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // Get inquiry data from session for auto-population
        $inquiryData = session('inquiry_data');
        
        // Clear inquiry data older than 30 minutes to prevent stale data
        if ($inquiryData && isset($inquiryData['timestamp'])) {
            $thirtyMinutesAgo = now()->subMinutes(30)->timestamp;
            if ($inquiryData['timestamp'] < $thirtyMinutesAgo) {
                session()->forget('inquiry_data');
                $inquiryData = null;
            }
        }
        
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'inquiryData' => $inquiryData
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Link existing inquiries and clients to the authenticated user
        $user = Auth::user();
        $inquiryLinkingService = app(\App\Services\InquiryLinkingService::class);
        $linkingResult = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Add linking result to session for display if any inquiries were linked
        if ($linkingResult['linked_inquiries'] > 0 || $linkingResult['linked_clients'] > 0) {
            session()->flash('inquiry_linking_success', $linkingResult['message']);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
