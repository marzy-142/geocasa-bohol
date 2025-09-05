<?php

use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\ApprovedBrokerController;
use App\Http\Controllers\Admin\BrokerApprovalController;
use App\Http\Controllers\Admin\ClientAssignmentController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Broker\DashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SellerRequestController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




// Health check routes
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/ping', [HealthController::class, 'ping'])->name('health.ping');

// Public routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/browse-properties', [PublicController::class, 'properties'])->name('public.properties');
Route::get('/browse-properties/{property:slug}', [PublicController::class, 'showProperty'])->name('public.properties.show');
Route::post('/browse-properties/{property:slug}/inquire', [PublicController::class, 'storeInquiry'])
    ->name('public.inquiries.store')
    ->middleware('throttle:5,1'); // 5 requests per minute

// Add the missing seller-requests.create route
Route::get('/sell-property', [SellerRequestController::class, 'create'])->name('seller-requests.create');
Route::post('/sell-property', [SellerRequestController::class, 'store'])
    ->name('seller-requests.store')
    ->middleware(['throttle:3,1', 'file.security', 'file.rate.limit']); // Enhanced security
Route::get('/sell-property/success', [SellerRequestController::class, 'success'])->name('seller-requests.success');

Route::middleware('auth')->group(function () {
    // Dashboard route - redirect based on role
       Route::get('/dashboard', function () {
    $user = auth()->user();
    
    return match($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'broker' => match($user->application_status) {
            'rejected' => redirect()->route('broker.rejected'),
            'approved' => redirect()->route('broker.dashboard'),
            default => redirect()->route('broker.pending-approval')
        },
        'client' => redirect()->route('client.dashboard'),
        default => abort(403, 'Invalid user role')
    };
})->name('dashboard')->middleware('auth');

    // Client routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    });

    // Broker routes - EXPANDED
    Route::prefix('broker')->name('broker.')->group(function () {
    // Pending approval route - accessible to any authenticated broker
    Route::middleware(['auth'])->group(function () {
        Route::get('/pending-approval', [DashboardController::class, 'pendingApproval'])
            ->name('pending-approval');
        Route::get('/rejected', [DashboardController::class, 'rejected'])
            ->name('rejected');
    });
    
    // Dashboard and other broker routes - require role AND approval
    Route::middleware(['auth', 'role:broker', 'broker.approved'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Property management
        Route::get('/properties', [PropertyController::class, 'brokerIndex'])->name('properties.index');
        Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::post('/properties', [PropertyController::class, 'store'])
            ->name('properties.store')
            ->middleware(['file.security', 'file.rate.limit']);
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('/properties/{property}', [PropertyController::class, 'update'])
            ->name('properties.update')
            ->middleware(['file.security', 'file.rate.limit']);
        
        // Client management
        // Change this line (around line 81):
        Route::get('/clients', [ClientController::class, 'brokerIndex'])->name('clients.index');
        
        // To this:
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        
        // Analytics and reports
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    });
});

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Document viewing route
        Route::get('/documents/view/{filename}', [\App\Http\Controllers\Admin\DocumentController::class, 'view'])
            ->name('documents.view')
            ->where('filename', '.*'); // Allow slashes in filename
        
        // User Management Routes (NEW)
        Route::resource('users', UserController::class);
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
        Route::post('users/bulk-actions', [UserController::class, 'bulkActions'])->name('users.bulk-actions');
        
        // Add admin properties routes
        Route::resource('properties', PropertyController::class);
        Route::post('properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])
            ->name('properties.toggle-featured');
        
        // Comprehensive Broker Management
        Route::get('/brokers', [\App\Http\Controllers\Admin\BrokerController::class, 'index'])->name('brokers.index');
        Route::get('/brokers/{broker}', [\App\Http\Controllers\Admin\BrokerController::class, 'show'])->name('brokers.show');
        Route::get('/brokers/{broker}/edit', [\App\Http\Controllers\Admin\BrokerController::class, 'edit'])->name('brokers.edit');
        Route::put('/brokers/{broker}', [\App\Http\Controllers\Admin\BrokerController::class, 'update'])->name('brokers.update');
        Route::post('/brokers/{broker}/status', [\App\Http\Controllers\Admin\BrokerController::class, 'updateStatus'])->name('brokers.update-status');
        Route::post('/brokers/{broker}/verification', [\App\Http\Controllers\Admin\BrokerController::class, 'updateVerification'])->name('brokers.update-verification');
        Route::post('/brokers/bulk-actions', [\App\Http\Controllers\Admin\BrokerController::class, 'bulkActions'])->name('brokers.bulk-actions');
        
        // Legacy Broker Approval Routes (for backward compatibility)
        Route::get('/broker-approvals', [BrokerApprovalController::class, 'index'])->name('broker-approvals.index');
        Route::get('/broker-approvals/{user}', [BrokerApprovalController::class, 'show'])->name('broker-approvals.show');
        Route::post('/broker-approvals/{user}/verify', [BrokerApprovalController::class, 'updateVerification'])->name('broker-approvals.verify');
        Route::post('/broker-approvals/{user}/approve', [BrokerApprovalController::class, 'approve'])->name('broker-approvals.approve');
        Route::delete('/broker-approvals/{user}/reject', [BrokerApprovalController::class, 'reject'])->name('broker-approvals.reject');
        
        // Client-Broker Assignment Management
        Route::get('/client-assignments', [ClientAssignmentController::class, 'index'])->name('client-assignments');
        Route::post('/client-assignments/assign', [ClientAssignmentController::class, 'assign'])->name('client-assignments.assign');
        Route::post('/client-assignments/bulk-assign', [ClientAssignmentController::class, 'bulkAssign'])->name('client-assignments.bulk-assign');
        Route::get('/broker-analytics', [ClientAssignmentController::class, 'getBrokerAnalytics'])->name('broker-analytics');
        Route::get('/broker-analytics-page', function() {
            return Inertia::render('Admin/BrokerAnalytics');
        })->name('broker-analytics-page');
        Route::get('/assignment-recommendations', [ClientAssignmentController::class, 'getAssignmentRecommendations'])->name('assignment-recommendations');
        
        // Client Management Routes
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::patch('/clients/{client}/assign-broker', [ClientController::class, 'assignBroker'])->name('clients.assign-broker');
        
        // Compliance Management Routes
        Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance.index');
        Route::get('/compliance/{report}', [ComplianceController::class, 'show'])->name('compliance.show');
        Route::post('/compliance', [ComplianceController::class, 'store'])->name('compliance.store');
        Route::patch('/compliance/{report}/status', [ComplianceController::class, 'updateStatus'])->name('compliance.update-status');
        Route::post('/compliance/bulk-update', [ComplianceController::class, 'bulkUpdate'])->name('compliance.bulk-update');
        
        // Investigation Workflow Routes
        Route::get('/investigations', [ComplianceController::class, 'investigationDashboard'])->name('investigations.dashboard');
        Route::post('/compliance/{report}/start-investigation', [ComplianceController::class, 'startInvestigation'])->name('compliance.start-investigation');
        Route::post('/compliance/{report}/collect-evidence', [ComplianceController::class, 'collectEvidence'])->name('compliance.collect-evidence');
        Route::post('/compliance/{report}/conduct-interview', [ComplianceController::class, 'conductInterview'])->name('compliance.conduct-interview');
        Route::post('/compliance/{report}/add-note', [ComplianceController::class, 'addInvestigationNote'])->name('compliance.add-note');
        Route::post('/compliance/{report}/escalate', [ComplianceController::class, 'escalateInvestigation'])->name('compliance.escalate');
        Route::post('/compliance/{report}/resolve-investigation', [ComplianceController::class, 'resolveInvestigation'])->name('compliance.resolve-investigation');
        Route::get('/compliance/{report}/timeline', [ComplianceController::class, 'getInvestigationTimeline'])->name('compliance.timeline');
        
        // Transaction Management Routes
        Route::get('/transactions', [TransactionController::class, 'adminIndex'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'adminShow'])->name('transactions.show');
        Route::post('/transactions/{transaction}/update-status', [TransactionController::class, 'adminUpdateStatus'])->name('transactions.update-status');
        
        // Reports routes
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.dashboard');
        Route::get('/reports/brokers', [ReportsController::class, 'brokers'])->name('reports.brokers');
        Route::get('/reports/properties', [ReportsController::class, 'properties'])->name('reports.properties');
        Route::get('/reports/inquiries', [ReportsController::class, 'inquiries'])->name('reports.inquiries');
        Route::get('/reports/compliance', [ReportsController::class, 'compliance'])->name('reports.compliance');
        
        // Admin Activity Audit Routes
        Route::get('/activity', [AdminActivityController::class, 'index'])->name('activity.index');
        Route::get('/activity/stats', [AdminActivityController::class, 'stats'])->name('activity.stats');
        Route::get('/activity/admin/{admin}', [AdminActivityController::class, 'byAdmin'])->name('activity.by-admin');
        Route::get('/activity/target/{targetType}/{targetId}', [AdminActivityController::class, 'byTarget'])->name('activity.by-target');
        Route::get('/activity/export', [AdminActivityController::class, 'export'])->name('activity.export');
        Route::get('/activity/summary', [AdminActivityController::class, 'summary'])->name('activity.summary');
        Route::post('/activity/cleanup', [AdminActivityController::class, 'cleanup'])->name('activity.cleanup');
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
    
    // Admin-only client-broker assignment routes
    Route::middleware(['role:admin'])->group(function () {
        Route::post('clients/{client}/assign-broker', [ClientController::class, 'assignBroker'])
            ->name('clients.assign-broker');
        Route::post('clients/bulk-assign-broker', [ClientController::class, 'bulkAssignBroker'])
            ->name('clients.bulk-assign-broker');
        Route::get('clients/unassigned/list', [ClientController::class, 'getUnassigned'])
            ->name('clients.unassigned');
    });
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
Route::delete('/notifications/{notification}', [NotificationController::class, 'delete'])->name('notifications.delete');
Route::get('/notifications/settings', [NotificationController::class, 'getSettings'])->name('notifications.settings');
Route::post('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.update-settings');
});

// Reminder routes
Route::middleware(['auth'])->group(function () {
    Route::get('/reminders', [ReminderController::class, 'dashboard'])->name('reminders.dashboard');
    Route::get('/api/reminders', [ReminderController::class, 'index'])->name('api.reminders.index');
    Route::get('/api/reminders/summary', [ReminderController::class, 'summary'])->name('api.reminders.summary');
    Route::get('/api/reminders/type/{type}', [ReminderController::class, 'byType'])->name('api.reminders.by-type');
    Route::get('/api/reminders/high-priority', [ReminderController::class, 'highPriority'])->name('api.reminders.high-priority');
    Route::post('/api/reminders/acknowledge', [ReminderController::class, 'acknowledge'])->name('api.reminders.acknowledge');
    Route::get('/api/reminders/preferences', [ReminderController::class, 'preferences'])->name('api.reminders.preferences');
    Route::post('/api/reminders/preferences', [ReminderController::class, 'updatePreferences'])->name('api.reminders.update-preferences');
});

// Messaging routes
Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.send-message');
    Route::post('/conversations/inquiry/{inquiry}', [ConversationController::class, 'createForInquiry'])->name('conversations.create-inquiry');
    Route::post('/conversations/transaction/{transaction}', [ConversationController::class, 'createForTransaction'])->name('conversations.create-transaction');
    Route::post('/conversations/{conversation}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
    Route::post('/conversations/{conversation}/unarchive', [ConversationController::class, 'unarchive'])->name('conversations.unarchive');
    Route::post('/conversations/{conversation}/mark-read', [ConversationController::class, 'markAsRead'])->name('conversations.mark-read');
});

// Leaderboard routes (public access)
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::get('/leaderboard/broker/{broker}', [LeaderboardController::class, 'show'])->name('leaderboard.broker');

require __DIR__.'/auth.php';

// Add broadcasting auth routes
// Broadcasting routes (should be after auth middleware is applied)
Broadcast::routes(['middleware' => ['web', 'auth']]);
// Add these routes in the admin group section
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Existing routes...
    
    // Approved Broker Management
    Route::get('/approved-brokers', [ApprovedBrokerController::class, 'index'])->name('approved-brokers.index');
    Route::get('/approved-brokers/{broker}', [ApprovedBrokerController::class, 'show'])->name('approved-brokers.show');
});
