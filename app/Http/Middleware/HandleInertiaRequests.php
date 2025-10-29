<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Prepare user data with avatar_url (don't modify the model directly)
        $userData = $user ? $user->toArray() : null;
        if ($user && $user->avatar) {
            $userData['avatar_url'] = asset('storage/' . $user->avatar) . '?v=' . time();
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Add notifications for authenticated users
            'notifications' => $user
                ? $user->notifications()->latest()->limit(10)->get()
                : [],
        ];
    }
}
