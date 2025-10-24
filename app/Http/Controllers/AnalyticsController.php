<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Services\PerformanceAnalyticsService;
use App\Services\AdaptiveWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected PerformanceAnalyticsService $analyticsService;
    protected AdaptiveWorkflowService $workflowService;

    public function __construct(
        PerformanceAnalyticsService $analyticsService,
        AdaptiveWorkflowService $workflowService
    ) {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
        $this->workflowService = $workflowService;
    }

    public function clientAnalytics(Request $request, Client $client)
    {
        $this->authorize('view', $client);
        
        $days = $request->get('days', 30);
        $analytics = $this->analyticsService->generateClientAnalytics($client, $days);

        return inertia('Analytics/Client', [
            'client' => $client->load(['user', 'broker']),
            'analytics' => $analytics,
            'period' => $days,
        ]);
    }

    public function brokerAnalytics(Request $request, User $broker)
    {
        $this->authorize('view', $broker);
        
        $days = $request->get('days', 30);
        $analytics = $this->analyticsService->generateBrokerAnalytics($broker, $days);

        return inertia('Analytics/Broker', [
            'broker' => $broker,
            'analytics' => $analytics,
            'period' => $days,
        ]);
    }

    public function systemAnalytics(Request $request)
    {
        $this->authorize('viewAny', User::class); // Admin only
        
        $days = $request->get('days', 30);
        $analytics = $this->analyticsService->generateSystemAnalytics($days);

        return inertia('Analytics/System', [
            'analytics' => $analytics,
            'period' => $days,
        ]);
    }

    public function clientBehaviorAnalysis(Client $client)
    {
        $this->authorize('view', $client);
        
        $behaviorAnalysis = $this->workflowService->analyzeClientBehavior($client);

        return response()->json([
            'success' => true,
            'behavior_analysis' => $behaviorAnalysis,
        ]);
    }

    public function transactionOutcomePrediction(Request $request, $transactionId)
    {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        $this->authorize('view', $transaction);
        
        $client = $transaction->client;
        $behaviorAnalysis = $this->workflowService->analyzeClientBehavior($client);
        $prediction = $this->workflowService->predictTransactionOutcome($transaction, $behaviorAnalysis);

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
            'behavior_analysis' => $behaviorAnalysis,
        ]);
    }

    public function adaptWorkflow(Request $request, $transactionId)
    {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        $this->authorize('update', $transaction);
        
        $client = $transaction->client;
        $behaviorAnalysis = $this->workflowService->analyzeClientBehavior($client);
        $adaptation = $this->workflowService->adaptWorkflowForTransaction($transaction, $behaviorAnalysis);

        return response()->json([
            'success' => $adaptation['success'],
            'adaptation' => $adaptation,
        ]);
    }

    public function trackEngagement(Request $request, Client $client)
    {
        $this->authorize('update', $client);
        
        $request->validate([
            'action' => 'required|string|max:100',
            'transaction_id' => 'nullable|exists:transactions,id',
            'data' => 'nullable|array',
        ]);

        $result = $this->analyticsService->trackClientEngagement(
            $client,
            $request->action,
            $request->only(['transaction_id', 'data'])
        );

        return response()->json($result);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:client,broker,system',
            'entity_id' => 'required_unless:type,system|integer',
            'days' => 'integer|min:1|max:365',
            'format' => 'string|in:json,pdf,excel',
        ]);

        $type = $request->type;
        $days = $request->get('days', 30);
        $format = $request->get('format', 'json');

        try {
            $entity = match ($type) {
                'client' => Client::findOrFail($request->entity_id),
                'broker' => User::where('role', 'broker')->findOrFail($request->entity_id),
                'system' => null,
            };

            $report = $this->analyticsService->generatePerformanceReport($type, $entity, $days);

            if ($format === 'json') {
                return response()->json([
                    'success' => true,
                    'report' => $report,
                ]);
            } else {
                // For PDF/Excel formats, you would generate and return the file
                return response()->json([
                    'success' => false,
                    'error' => 'PDF and Excel formats not yet implemented',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $type,
                'entity_id' => $request->entity_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate report.',
            ], 500);
        }
    }

    public function clientSatisfactionScore(Client $client)
    {
        $this->authorize('view', $client);
        
        $satisfactionData = $this->analyticsService->calculateClientSatisfactionScore($client);

        return response()->json([
            'success' => true,
            'satisfaction' => $satisfactionData,
        ]);
    }

    public function optimizeCommunicationTiming(Client $client)
    {
        $this->authorize('update', $client);
        
        $behaviorAnalysis = $this->workflowService->analyzeClientBehavior($client);
        $optimization = $this->workflowService->optimizeCommunicationTiming($client, $behaviorAnalysis);

        return response()->json([
            'success' => $optimization['success'],
            'optimization' => $optimization,
        ]);
    }

    public function createDynamicReminders(Request $request, $transactionId)
    {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        $this->authorize('update', $transaction);
        
        $client = $transaction->client;
        $behaviorAnalysis = $this->workflowService->analyzeClientBehavior($client);
        $reminders = $this->workflowService->createDynamicReminders($transaction, $behaviorAnalysis);

        return response()->json([
            'success' => $reminders['success'],
            'reminders' => $reminders,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $days = $request->get('days', 30);

        try {
            if ($user->role === 'admin') {
                $analytics = $this->analyticsService->generateSystemAnalytics($days);
            } elseif ($user->role === 'broker') {
                $analytics = $this->analyticsService->generateBrokerAnalytics($user, $days);
            } else {
                $client = $user->client;
                if ($client) {
                    $analytics = $this->analyticsService->generateClientAnalytics($client, $days);
                } else {
                    $analytics = $this->analyticsService->getDefaultAnalytics();
                }
            }

            return inertia('Analytics/Dashboard', [
                'user' => $user,
                'analytics' => $analytics,
                'period' => $days,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate analytics dashboard', [
                'user_id' => $user->id,
                'role' => $user->role,
                'error' => $e->getMessage(),
            ]);

            return inertia('Analytics/Dashboard', [
                'user' => $user,
                'analytics' => $this->getDefaultDashboardData($user),
                'period' => $days,
                'error' => 'Failed to load analytics data.',
            ]);
        }
    }

    protected function getDefaultDashboardData(User $user): array
    {
        return match ($user->role) {
            'admin' => $this->analyticsService->getDefaultSystemAnalytics(),
            'broker' => $this->analyticsService->getDefaultBrokerAnalytics(),
            default => $this->analyticsService->getDefaultAnalytics(),
        };
    }
}