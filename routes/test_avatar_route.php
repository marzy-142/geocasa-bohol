<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/test-avatar-debug', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    // Manually add avatar_url like the middleware does
    if ($user->avatar) {
        $user->avatar_url = asset('storage/' . $user->avatar) . '?v=' . time();
    }
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url ?? null,
        ],
        'debug' => [
            'avatar_exists' => !empty($user->avatar),
            'avatar_url_set' => !empty($user->avatar_url),
            'full_path' => storage_path('app/public/' . $user->avatar),
            'public_url' => asset('storage/' . $user->avatar),
            'file_exists' => $user->avatar ? file_exists(storage_path('app/public/' . $user->avatar)) : false,
        ]
    ]);
})->middleware('auth');
