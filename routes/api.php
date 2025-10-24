<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SellerRequestController;
use App\Http\Controllers\Api\BrokerAnalyticsController;
use App\Http\Controllers\Api\CommunicationWorkflowController;
use App\Http\Controllers\Api\EnhancedAssignmentController;
use App\Http\Controllers\Api\PerformanceOptimizationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Global search route (authenticated)
Route::middleware(['auth:sanctum', 'api.rate.limit:30,1'])->group(function () {
    Route::post('/search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('api.search');
});



// Public API routes
Route::prefix('v1')->group(function () {
    // Authentication routes - stricter rate limiting
    Route::middleware(['api.rate.limit:5,1'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
    
    // Broker registration endpoints (public) - moderate rate limiting
    Route::middleware(['api.rate.limit:20,1'])->prefix('broker-registration')->group(function () {
        Route::post('/draft/save', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'saveDraft']);
        Route::get('/draft/get', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'getDraft']);
        Route::put('/draft/update', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'updateDraft']);
        Route::delete('/draft/delete', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'deleteDraft']);
        Route::post('/prc/verify', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'verifyPRCLicense']);
        Route::get('/prc/status', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'getPRCVerificationStatus']);
        Route::post('/session/generate', [\App\Http\Controllers\Api\BrokerRegistrationController::class, 'generateSessionId']);
    });

    // Public property routes - moderate rate limiting
    Route::middleware(['api.rate.limit:30,1'])->group(function () {
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/properties/search', [PropertyController::class, 'search']);
        Route::get('/properties/featured', [PropertyController::class, 'featured']);
        Route::get('/properties/{property}', [PropertyController::class, 'show']);
    });
    
    // Public seller request - strict rate limiting
    Route::post('/seller-requests', [SellerRequestController::class, 'store'])
        ->middleware(['api.rate.limit:3,1', 'file.security', 'file.rate.limit']);
});

// Protected API routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // User authentication - moderate rate limiting
    Route::middleware(['api.rate.limit:10,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::post('/user/change-password', [UserController::class, 'changePassword']);
    });
    
    // User inquiries - standard rate limiting
    Route::middleware(['api.rate.limit:20,1'])->group(function () {
        Route::get('/inquiries', [InquiryController::class, 'index']);
        Route::post('/inquiries', [InquiryController::class, 'store']);
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show']);
    });
    
    // Broker routes
    Route::middleware(['role:broker', 'api.rate.limit:40,1'])->prefix('broker')->group(function () {
        // Property management - with file security for create/update
        Route::get('/properties', [PropertyController::class, 'brokerProperties']);
        Route::post('/properties', [PropertyController::class, 'store'])
            ->middleware(['file.security', 'file.rate.limit']);
        Route::get('/properties/{property}', [PropertyController::class, 'brokerShow']);
        Route::put('/properties/{property}', [PropertyController::class, 'update'])
            ->middleware(['file.security', 'file.rate.limit']);
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);
        
        // Inquiry management
        Route::get('/inquiries', [InquiryController::class, 'brokerInquiries']);
        Route::put('/inquiries/{inquiry}', [InquiryController::class, 'updateStatus']);
        
        // Seller request management
        Route::get('/seller-requests', [SellerRequestController::class, 'index']);
        Route::get('/seller-requests/{sellerRequest}', [SellerRequestController::class, 'show']);
        Route::put('/seller-requests/{sellerRequest}/status', [SellerRequestController::class, 'updateStatus']);
        
        // Client management for brokers
        Route::get('/clients', [\App\Http\Controllers\Api\ClientController::class, 'brokerClients']);
        Route::get('/clients/{client}', [\App\Http\Controllers\Api\ClientController::class, 'brokerShow']);
        
        // Broker analytics (self-access)
        Route::prefix('analytics')->group(function () {
            Route::get('/performance', [BrokerAnalyticsController::class, 'getPerformanceMetrics']);
            Route::get('/workload', [BrokerAnalyticsController::class, 'getWorkload']);
            Route::get('/relationships', [BrokerAnalyticsController::class, 'getRelationshipAnalytics']);
            Route::get('/communication', [BrokerAnalyticsController::class, 'getCommunicationEffectiveness']);
            Route::get('/report', [BrokerAnalyticsController::class, 'generatePerformanceReport']);
            Route::get('/trends', [BrokerAnalyticsController::class, 'getPerformanceTrends']);
        });
        
        // Broker workflow management (self-access)
        Route::prefix('workflows')->group(function () {
            Route::get('/', [CommunicationWorkflowController::class, 'getBrokerWorkflows']);
            Route::get('/client/{clientId}', [CommunicationWorkflowController::class, 'getClientWorkflows']);
            Route::get('/{workflowId}', [CommunicationWorkflowController::class, 'getWorkflowDetails']);
            Route::put('/{workflowId}/status', [CommunicationWorkflowController::class, 'updateWorkflowStatus']);
            Route::post('/sync-transaction', [CommunicationWorkflowController::class, 'syncConversationWithTransaction']);
        });
    });
    
    // Admin routes - higher rate limits for administrative tasks
    Route::middleware(['role:admin', 'api.rate.limit:100,1'])->prefix('admin')->group(function () {
        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        
        // All properties
        Route::get('/properties', [PropertyController::class, 'adminProperties']);
        Route::put('/properties/{property}/approve', [PropertyController::class, 'approve']);
        Route::put('/properties/{property}/reject', [PropertyController::class, 'reject']);
        
        // All inquiries
        Route::get('/inquiries', [InquiryController::class, 'adminInquiries']);
        
        // All seller requests
        Route::get('/seller-requests', [SellerRequestController::class, 'adminIndex']);
        Route::put('/seller-requests/{sellerRequest}/assign-broker', [SellerRequestController::class, 'assignBroker']);
        
        // Client-broker assignments
        Route::get('/clients', [\App\Http\Controllers\Api\ClientController::class, 'index']);
        Route::get('/clients/{client}', [\App\Http\Controllers\Api\ClientController::class, 'show']);
        Route::post('/clients/{client}/assign-broker', [\App\Http\Controllers\Api\ClientController::class, 'assignBroker']);
        Route::post('/clients/bulk-assign-broker', [\App\Http\Controllers\Api\ClientController::class, 'bulkAssignBroker']);
        Route::get('/clients/unassigned', [\App\Http\Controllers\Api\ClientController::class, 'getUnassigned']);
        Route::get('/broker-analytics', [\App\Http\Controllers\Api\ClientController::class, 'getBrokerAnalytics']);
        Route::get('/assignment-recommendations', [\App\Http\Controllers\Api\ClientController::class, 'getAssignmentRecommendations']);
        
        // Enhanced broker analytics
        Route::prefix('analytics')->group(function () {
            Route::get('/brokers/{brokerId?}', [BrokerAnalyticsController::class, 'getPerformanceMetrics']);
            Route::get('/brokers/{brokerId?}/workload', [BrokerAnalyticsController::class, 'getWorkload']);
            Route::get('/brokers/{brokerId?}/relationships', [BrokerAnalyticsController::class, 'getRelationshipAnalytics']);
            Route::get('/brokers/{brokerId?}/communication', [BrokerAnalyticsController::class, 'getCommunicationEffectiveness']);
            Route::get('/brokers/{brokerId?}/report', [BrokerAnalyticsController::class, 'generatePerformanceReport']);
            Route::get('/brokers/{brokerId?}/trends', [BrokerAnalyticsController::class, 'getPerformanceTrends']);
            Route::get('/brokers', [BrokerAnalyticsController::class, 'getAllBrokersPerformance']);
            Route::get('/recommendations', [BrokerAnalyticsController::class, 'getAssignmentRecommendations']);
        });
        
        // Enhanced assignment management
        Route::prefix('assignments')->group(function () {
            Route::post('/clients/{client}', [EnhancedAssignmentController::class, 'assignBrokerToClient']);
            Route::post('/inquiries/{inquiry}', [EnhancedAssignmentController::class, 'assignBrokerToInquiry']);
            Route::post('/reassign', [EnhancedAssignmentController::class, 'reassignBroker']);
            Route::get('/recommendations', [EnhancedAssignmentController::class, 'getAssignmentRecommendations']);
            Route::post('/bulk-assign-clients', [EnhancedAssignmentController::class, 'bulkAssignClients']);
            Route::get('/history', [EnhancedAssignmentController::class, 'getAssignmentHistory']);
        });
        
        // Communication workflow management
        Route::prefix('workflows')->group(function () {
            Route::get('/statistics', [CommunicationWorkflowController::class, 'getWorkflowStatistics']);
            Route::get('/overdue', [CommunicationWorkflowController::class, 'getOverdueWorkflows']);
            Route::post('/process-pending', [CommunicationWorkflowController::class, 'processPendingWorkflows']);
            Route::post('/auto-escalate', [CommunicationWorkflowController::class, 'autoEscalateInquiries']);
            Route::post('/inquiries/{inquiry}/workflow', [CommunicationWorkflowController::class, 'createInquiryWorkflow']);
            Route::post('/transactions/{transaction}/workflow', [CommunicationWorkflowController::class, 'createTransactionWorkflow']);
            Route::post('/sync-transaction', [CommunicationWorkflowController::class, 'syncConversationWithTransaction']);
        });
        
        // Performance optimization management
        Route::prefix('performance')->group(function () {
            Route::get('/stats', [PerformanceOptimizationController::class, 'getStats']);
            Route::post('/optimize', [PerformanceOptimizationController::class, 'runOptimization']);
            Route::get('/recommendations', [PerformanceOptimizationController::class, 'getRecommendations']);
            Route::get('/health', [PerformanceOptimizationController::class, 'getHealth']);
            Route::get('/metrics', [PerformanceOptimizationController::class, 'getPerformanceMetrics']);
            Route::post('/cache/clear', [PerformanceOptimizationController::class, 'clearCaches']);
            Route::post('/cache/warm', [PerformanceOptimizationController::class, 'warmUpCaches']);
            Route::get('/summary', [PerformanceOptimizationController::class, 'getSummary']);
        });
    });
    
    // Reminder API routes
    Route::get('/reminders', [\App\Http\Controllers\ReminderController::class, 'index']);
    Route::get('/reminders/summary', [\App\Http\Controllers\ReminderController::class, 'summary']);
    Route::get('/reminders/type/{type}', [\App\Http\Controllers\ReminderController::class, 'byType']);
    Route::get('/reminders/high-priority', [\App\Http\Controllers\ReminderController::class, 'highPriority']);
    Route::post('/reminders/acknowledge', [\App\Http\Controllers\ReminderController::class, 'acknowledge']);
    Route::get('/reminders/preferences', [\App\Http\Controllers\ReminderController::class, 'preferences']);
    Route::post('/reminders/preferences', [\App\Http\Controllers\ReminderController::class, 'updatePreferences']);
});