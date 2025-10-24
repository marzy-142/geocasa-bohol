<?php

use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\ApprovedBrokerController;
use App\Http\Controllers\Admin\BrokerAnalyticsController;
use App\Http\Controllers\Admin\BrokerApprovalController;
use App\Http\Controllers\Admin\BrokerController;
use App\Http\Controllers\Admin\ClientAssignmentController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Broker\DashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PropertyController as ClientPropertyController;
use App\Http\Controllers\Client\BrokerController as ClientBrokerController;
use App\Http\Controllers\Client\TransactionController as ClientTransactionController;
use App\Http\Controllers\Client\DocumentController as ClientDocumentController;
use App\Http\Controllers\Client\MeetingController as ClientMeetingController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\Client\InquiryController as ClientInquiryController;
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
Route::post('/store-inquiry-session', [PublicController::class, 'storeInquirySession'])
    ->name('public.store-inquiry-session')
    ->middleware('throttle:10,1'); // 10 requests per minute for session storage

// Test route for 360 viewer
Route::get('/test-360-viewer', function () {
    return Inertia::render('Test360Viewer');
})->name('test.360viewer');

// Seller requests routes
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

    // Global search route
    Route::post('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');

    // Client routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/original', [ClientDashboardController::class, 'dashboard'])->name('dashboard.original');
        Route::get('/properties', [\App\Http\Controllers\Client\PropertyController::class, 'index'])->name('properties');
        // Specific routes MUST come before dynamic routes
        Route::get('/properties/saved', [\App\Http\Controllers\Client\PropertyController::class, 'saved'])->name('properties.saved');
        Route::get('/properties/compare', [\App\Http\Controllers\Client\PropertyController::class, 'compare'])->name('properties.compare');
        
        // Action routes - using /save-property instead of /property-save
        Route::post('/save-property/{property}', [\App\Http\Controllers\Client\PropertyController::class, 'save'])->name('properties.save');
        Route::post('/unsave-property/{property}', [\App\Http\Controllers\Client\PropertyController::class, 'unsave'])->name('properties.unsave');
        Route::post('/track-property/{property}', [ClientDashboardController::class, 'trackPropertyView'])->name('properties.track-view');
        
        // Dynamic property route comes last
        Route::get('/properties/{property}', [\App\Http\Controllers\Client\PropertyController::class, 'show'])->name('properties.show');
        Route::post('/favorite-areas', [ClientDashboardController::class, 'addFavoriteArea'])->name('favorite-areas.add');
        
        // Saved Searches
        Route::prefix('searches')->name('searches.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\SavedSearchController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Client\SavedSearchController::class, 'store'])->name('store');
            Route::delete('/{search}', [\App\Http\Controllers\Client\SavedSearchController::class, 'destroy'])->name('destroy');
            Route::post('/{search}/toggle-notifications', [\App\Http\Controllers\Client\SavedSearchController::class, 'toggleNotifications'])->name('toggle-notifications');
        });
        
        // Seller Requests (Client selling their property)
        Route::prefix('seller-requests')->name('seller-requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\SellerRequestController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Client\SellerRequestController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Client\SellerRequestController::class, 'store'])->name('store');
            Route::get('/{sellerRequest}', [\App\Http\Controllers\Client\SellerRequestController::class, 'show'])->name('show');
        });
        
        // Client Transaction Routes
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [ClientTransactionController::class, 'dashboard'])->name('dashboard');
            Route::get('/{transaction}', [ClientTransactionController::class, 'show'])->name('show');
            Route::patch('/{transaction}', [ClientTransactionController::class, 'update'])->name('update');
            Route::post('/{transaction}/approve', [ClientTransactionController::class, 'processApproval'])->name('approve');
            
            // Document management routes
            Route::prefix('{transaction}/documents')->name('documents.')->group(function () {
                Route::get('/', [ClientDocumentController::class, 'index'])->name('index');
                Route::post('/', [ClientDocumentController::class, 'store'])->name('store');
                Route::get('/{documentId}/download', [ClientDocumentController::class, 'download'])->name('download');
                Route::delete('/{documentId}', [ClientDocumentController::class, 'destroy'])->name('destroy');
            });
            
            // Meeting routes
            Route::prefix('{transaction}/meetings')->name('meetings.')->group(function () {
                Route::get('/', [ClientMeetingController::class, 'getTransactionMeetings'])->name('index');
                Route::post('/', [ClientMeetingController::class, 'store'])->name('store');
                Route::get('/{meeting}', [ClientMeetingController::class, 'show'])->name('show');
                Route::patch('/{meeting}', [ClientMeetingController::class, 'update'])->name('update');
                Route::post('/{meeting}/cancel', [ClientMeetingController::class, 'cancel'])->name('cancel');
            });
        });
        
        // Client Meeting Routes (standalone)
        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [ClientMeetingController::class, 'index'])->name('index');
            Route::get('/{meeting}', [ClientMeetingController::class, 'show'])->name('show');
        });
        Route::get('/inquiries', [ClientInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/create', [ClientInquiryController::class, 'create'])->name('inquiries.create');
        Route::post('/inquiries', [ClientInquiryController::class, 'store'])->name('inquiries.store');
        Route::get('/inquiries/{inquiry}', [ClientInquiryController::class, 'show'])->name('inquiries.show');
        Route::put('/inquiries/{inquiry}', [ClientInquiryController::class, 'update'])->name('inquiries.update');
        Route::get('/broker', [ClientBrokerController::class, 'show'])->name('broker');
        Route::post('/broker/message', [ClientBrokerController::class, 'sendMessage'])->name('broker.message');
        Route::post('/broker/meeting', [ClientBrokerController::class, 'scheduleMeeting'])->name('broker.meeting');
        Route::delete('/broker/meeting/{meeting}', [ClientBrokerController::class, 'cancelMeeting'])->name('broker.meeting.cancel');
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
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        
        // Enhanced Transaction Management Routes
    });
});

// Broker Properties Routes - Outside prefix to use /properties directly
Route::middleware(['auth', 'role:broker', 'broker.approved'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'brokerIndex'])->name('broker.properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('broker.properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('broker.properties.store');
    
    // Property Renewal Routes - MUST come before parameterized routes
    Route::get('/properties/renewals', [PropertyController::class, 'renewals'])->name('broker.properties.renewals');
    
    // Parameterized routes - MUST come after specific routes
    Route::get('/properties/{property}', [PropertyController::class, 'brokerShow'])->name('broker.properties.show');
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('broker.properties.edit');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('broker.properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('broker.properties.destroy');
    Route::post('/properties/{property}/renew', [PropertyController::class, 'renew'])->name('broker.properties.renew');
    
    // Featured Property Routes
    Route::post('/properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])->name('broker.properties.toggle-featured');
    Route::post('/properties/{property}/auto-feature', [PropertyController::class, 'autoFeature'])->name('broker.properties.auto-feature');
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
    
    // Admin properties routes - now properly namespaced
    Route::resource('properties', PropertyController::class);
    Route::post('properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])
        ->name('properties.toggle-featured');
    
    // Comprehensive Broker Management
    Route::get('/brokers', [BrokerController::class, 'index'])->name('brokers.index');
    Route::get('/brokers/{broker}', [BrokerController::class, 'show'])->name('brokers.show');
    Route::get('/brokers/{broker}/edit', [BrokerController::class, 'edit'])->name('brokers.edit');
    Route::put('/brokers/{broker}', [BrokerController::class, 'update'])->name('brokers.update');
    Route::post('/brokers/{broker}/status', [BrokerController::class, 'updateStatus'])->name('brokers.update-status');
    Route::post('/brokers/{broker}/verification', [BrokerController::class, 'updateVerification'])->name('brokers.update-verification');
    Route::post('/brokers/bulk-actions', [BrokerController::class, 'bulkActions'])->name('brokers.bulk-actions');
    
    // Additional Broker Management Routes
    Route::get('/brokers/{broker}/properties', [BrokerController::class, 'properties'])->name('brokers.properties');
    Route::get('/brokers/{broker}/transactions', [BrokerController::class, 'transactions'])->name('brokers.transactions');
    Route::get('/brokers/applications/export', [BrokerController::class, 'exportApplications'])->name('brokers.applications.export');
    Route::post('/brokers/documents/verify', [BrokerController::class, 'verifyDocument'])->name('brokers.documents.verify');
    Route::get('/brokers/{broker}/documents/report', [BrokerController::class, 'documentReport'])->name('brokers.documents.report');
    Route::post('/brokers/communications/send', [BrokerController::class, 'sendCommunication'])->name('brokers.communications.send');
    Route::post('/brokers/communications/draft', [BrokerController::class, 'draftCommunication'])->name('brokers.communications.draft');
    Route::post('/brokers/communications/schedule', [BrokerController::class, 'scheduleCommunication'])->name('brokers.communications.schedule');
    Route::post('/brokers/communications/templates/store', [BrokerController::class, 'storeTemplate'])->name('brokers.communications.templates.store');
    
    // Legacy Broker Approval Routes (for backward compatibility)
    Route::get('/broker-approvals', [BrokerApprovalController::class, 'index'])->name('broker-approvals.index');
    Route::get('/broker-approvals/{user}', [BrokerApprovalController::class, 'show'])->name('broker-approvals.show');
    Route::post('/broker-approvals/{user}/verify', [BrokerApprovalController::class, 'updateVerification'])->name('broker-approvals.verify');
    Route::post('/broker-approvals/{user}/approve', [BrokerApprovalController::class, 'approve'])->name('broker-approvals.approve');
    Route::delete('/broker-approvals/{user}/reject', [BrokerApprovalController::class, 'reject'])->name('broker-approvals.reject');
    
    // Seller Request Assignment Management (consolidated from client-assignments)
    Route::post('/seller-requests/assign', [SellerRequestController::class, 'assign'])->name('seller-requests.assign');
    Route::post('/seller-requests/bulk-assign', [SellerRequestController::class, 'bulkAssign'])->name('seller-requests.bulk-assign');
    Route::get('/seller-requests/broker-analytics', [SellerRequestController::class, 'getBrokerAnalytics'])->name('seller-requests.broker-analytics');
    Route::get('/seller-requests/assignment-recommendations', [SellerRequestController::class, 'getAssignmentRecommendations'])->name('seller-requests.assignment-recommendations');
    
    // Client Assignment and Analytics Routes
    Route::get('/client-assignments', [ClientAssignmentController::class, 'index'])->name('client-assignments');
    Route::post('/seller-requests/assign', [ClientAssignmentController::class, 'assign'])->name('seller-requests.assign');
    Route::post('/seller-requests/bulk-assign', [ClientAssignmentController::class, 'bulkAssign'])->name('admin.seller-requests.bulk-assign');
    Route::get('/broker-analytics', [BrokerAnalyticsController::class, 'index'])->name('broker-analytics');
    Route::get('/broker-analytics-page', [BrokerAnalyticsController::class, 'analyticsPage'])->name('broker-analytics-page');
    Route::get('/assignment-recommendations', [SellerRequestController::class, 'getAssignmentRecommendations'])->name('assignment-recommendations');
    
    // Admin Inquiry Management
    Route::get('/inquiries', [InquiryController::class, 'adminIndex'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
    Route::post('/inquiries/{inquiry}/reassign', [InquiryController::class, 'reassign'])->name('inquiries.reassign');
    Route::post('/inquiries/{inquiry}/flag', [InquiryController::class, 'flag'])->name('inquiries.flag');
    Route::get('/inquiries/export', [InquiryController::class, 'export'])->name('inquiries.export');
    
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
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/{transaction}/update-status', [TransactionController::class, 'adminUpdateStatus'])->name('transactions.update-status');
    
    // Admin Transaction Analytics & Management
    Route::get('/transactions/statistics', [TransactionController::class, 'adminStatistics'])->name('transactions.statistics');
    Route::get('/transactions/export', [TransactionController::class, 'adminExport'])->name('transactions.export');
    Route::post('/transactions/bulk-update', [TransactionController::class, 'adminBulkUpdate'])->name('transactions.bulk-update');
    
    // Reports routes
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.dashboard');
    Route::get('/reports/brokers', [ReportsController::class, 'brokers'])->name('reports.brokers');
    Route::get('/reports/properties', [ReportsController::class, 'properties'])->name('reports.properties');
    Route::get('/reports/inquiries', [ReportsController::class, 'inquiries'])->name('reports.inquiries');
    Route::get('/reports/compliance', [ReportsController::class, 'compliance'])->name('reports.compliance');
    
    // Broker Analytics routes
    Route::get('/analytics/brokers', [BrokerAnalyticsController::class, 'index'])->name('analytics.brokers.index');
    Route::get('/analytics/brokers/{broker}', [BrokerAnalyticsController::class, 'show'])->name('analytics.brokers.show');
    Route::get('/analytics/brokers/export', [BrokerAnalyticsController::class, 'export'])->name('analytics.brokers.export');
    Route::get('/analytics/brokers/{broker}/export', [BrokerAnalyticsController::class, 'exportBroker'])->name('analytics.brokers.export-broker');
    
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

// REMOVE THIS ENTIRE BLOCK - This was causing the 403 error:
// Property routes
// Route::middleware(['auth'])->group(function () {
//     Route::resource('properties', PropertyController::class);
//     Route::post('properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])
//         ->name('properties.toggle-featured');
// });

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
    Route::post('inquiries/{inquiry}/accept', [InquiryController::class, 'accept'])
        ->name('inquiries.accept');
});

// Analytics routes
Route::middleware(['auth'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/client/{client}', [AnalyticsController::class, 'clientAnalytics'])->name('client');
    Route::get('/broker/{broker}', [AnalyticsController::class, 'brokerAnalytics'])->name('broker');
    Route::get('/system', [AnalyticsController::class, 'systemAnalytics'])->name('system');
    Route::post('/report', [AnalyticsController::class, 'generateReport'])->name('report');
    
    // Client behavior analysis
    Route::get('/client/{client}/behavior', [AnalyticsController::class, 'clientBehaviorAnalysis'])->name('client.behavior');
    Route::post('/client/{client}/engagement', [AnalyticsController::class, 'trackEngagement'])->name('client.engagement');
    Route::post('/client/{client}/optimize-timing', [AnalyticsController::class, 'optimizeCommunicationTiming'])->name('client.optimize-timing');
    Route::get('/client/{client}/satisfaction', [AnalyticsController::class, 'clientSatisfactionScore'])->name('client.satisfaction');
    
    // Transaction predictions and adaptations
    Route::get('/transaction/{transactionId}/prediction', [AnalyticsController::class, 'transactionOutcomePrediction'])->name('transaction.prediction');
    Route::post('/transaction/{transactionId}/adapt-workflow', [AnalyticsController::class, 'adaptWorkflow'])->name('transaction.adapt-workflow');
    Route::post('/transaction/{transactionId}/reminders', [AnalyticsController::class, 'createDynamicReminders'])->name('transaction.reminders');
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
});

// Messaging routes with rate limiting
Route::middleware(['auth'])->group(function () {
    // Read operations (higher limit)
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    
    // Write operations (rate limited to prevent spam)
    Route::middleware(['throttle:messages'])->group(function () {
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.send-message');
        Route::post('/conversations/inquiry/{inquiry}', [ConversationController::class, 'createForInquiry'])->name('conversations.create-inquiry');
        Route::post('/conversations/transaction/{transaction}', [ConversationController::class, 'createForTransaction'])->name('conversations.create-transaction');
    });
    
    // Other operations (moderate limit)
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::post('/conversations/{conversation}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
        Route::post('/conversations/{conversation}/unarchive', [ConversationController::class, 'unarchive'])->name('conversations.unarchive');
        Route::post('/conversations/{conversation}/mark-read', [ConversationController::class, 'markAsRead'])->name('conversations.mark-read');
    });
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
