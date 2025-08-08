<?php

use App\Http\Controllers\Admin\BrokerApprovalController;
use App\Http\Controllers\Broker\DashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SellerRequestController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/browse-properties', [PublicController::class, 'properties'])->name('public.properties');
Route::get('/browse-properties/{property:slug}', [PublicController::class, 'showProperty'])->name('public.properties.show');
Route::post('/browse-properties/{property:slug}/inquire', [PublicController::class, 'storeInquiry'])->name('public.inquiries.store');

// Public seller request routes
Route::get('/sell-property', [SellerRequestController::class, 'create'])->name('seller-requests.create');
Route::post('/sell-property', [SellerRequestController::class, 'store'])->name('seller-requests.store');
Route::get('/sell-property/success', [SellerRequestController::class, 'success'])->name('seller-requests.success');

Route::middleware('auth')->group(function () {
    // Dashboard route - redirect based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'broker') {
            // Check if broker is approved
            if ($user->is_approved) {
                return redirect()->route('broker.dashboard');
            } else {
                return redirect()->route('broker.pending-approval');
            }
        } else {
            return redirect()->route('client.dashboard');
        }
    })->name('dashboard');

    // Client routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    });

    // Broker routes - COMPLETELY RESTRUCTURED
    Route::prefix('broker')->name('broker.')->group(function () {
        // Pending approval route - NO role middleware to avoid conflicts
        Route::get('/pending-approval', [DashboardController::class, 'pendingApproval'])
            ->name('pending-approval')
            ->middleware('auth'); // Only require authentication
        
        // Dashboard and other broker routes - require role AND approval
        Route::middleware(['role:broker', 'broker.approved'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        });
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return inertia('Admin/Dashboard');
        })->name('dashboard');
        
        Route::get('/brokers', [BrokerApprovalController::class, 'index'])->name('brokers.index');
        Route::get('/brokers/{user}', [BrokerApprovalController::class, 'show'])->name('brokers.show');
        Route::post('/brokers/{user}/approve', [BrokerApprovalController::class, 'approve'])->name('brokers.approve');
        Route::delete('/brokers/{user}/reject', [BrokerApprovalController::class, 'reject'])->name('brokers.reject');
    });

    // Logout route
    Route::post('/logout', function () {
        auth()->logout();
        return redirect()->route('home');
    })->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Property routes
Route::middleware(['auth'])->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::post('properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])
        ->name('properties.toggle-featured');
});

// Client routes
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);
});

// Inquiry routes
Route::middleware(['auth'])->group(function () {
    Route::resource('inquiries', InquiryController::class);
    Route::post('inquiries/{inquiry}/respond', [InquiryController::class, 'respond'])
        ->name('inquiries.respond');
});

// Transaction routes
Route::middleware(['auth'])->group(function () {
    Route::resource('transactions', TransactionController::class);
    Route::post('transactions/{transaction}/update-status', [TransactionController::class, 'updateStatus'])
        ->name('transactions.update-status');
});

// Seller Request routes (protected)
Route::middleware(['auth'])->group(function () {
    Route::resource('seller-requests', SellerRequestController::class)->except(['create', 'store']);
    Route::post('seller-requests/{sellerRequest}/update-status', [SellerRequestController::class, 'updateStatus'])
        ->name('seller-requests.update-status');
    Route::post('seller-requests/{sellerRequest}/convert-to-property', [SellerRequestController::class, 'convertToProperty'])
        ->name('seller-requests.convert-to-property');
});

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
});

// Leaderboard routes (public access)
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::get('/leaderboard/broker/{broker}', [LeaderboardController::class, 'show'])->name('leaderboard.broker');

require __DIR__.'/auth.php';
