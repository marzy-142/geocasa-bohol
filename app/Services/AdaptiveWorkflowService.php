<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\CommunicationWorkflow;
use App\Models\ClientTransactionEngagement;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdaptiveWorkflowService
{
    protected CommunicationWorkflowService $workflowService;
    protected ClientNotificationService $notificationService;

    public function __construct(
        CommunicationWorkflowService $workflowService,
        ClientNotificationService $notificationService
    ) {
        $this->workflowService = $workflowService;
        $this->notificationService = $notificationService;
    }

    public function analyzeClientBehavior(Client $client): array
    {
        try {
            $engagementData = $this->getClientEngagementData($client);
            $transactionHistory = $this->getTransactionHistory($client);
            $responsePatterns = $this->analyzeResponsePatterns($client);
            $preferences = $this->analyzePreferences($client);

            return [
                'engagement_level' => $this->calculateEngagementLevel($engagementData),
                'response_time' => $responsePatterns['average_response_time'],
                'preferred_communication' => $preferences['communication_method'],
                'preferred_timing' => $preferences['communication_timing'],
                'transaction_velocity' => $this->calculateTransactionVelocity($transactionHistory),
                'risk_factors' => $this->identifyRiskFactors($engagementData, $transactionHistory),
                'recommendations' => $this->generateRecommendations($engagementData, $responsePatterns, $preferences),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to analyze client behavior', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'engagement_level' => 'medium',
                'response_time' => 24,
                'preferred_communication' => 'email',
                'preferred_timing' => 'business_hours',
                'transaction_velocity' => 'normal',
                'risk_factors' => [],
                'recommendations' => [],
            ];
        }
    }

    public function adaptWorkflowForTransaction(Transaction $transaction, array $behaviorAnalysis): array
    {
        try {
            $client = $transaction->client;
            $currentWorkflows = $transaction->communicationWorkflows()->active()->get();
            
            // Remove workflows that don't match client behavior
            $this->removeInappropriateWorkflows($currentWorkflows, $behaviorAnalysis);
            
            // Create new adaptive workflows
            $newWorkflows = $this->createAdaptiveWorkflows($transaction, $behaviorAnalysis);
            
            // Adjust existing workflows based on behavior
            $this->adjustExistingWorkflows($currentWorkflows, $behaviorAnalysis);

            Log::info('Workflow adapted for transaction', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'engagement_level' => $behaviorAnalysis['engagement_level'],
                'new_workflows_created' => count($newWorkflows),
                'workflows_adjusted' => $currentWorkflows->count(),
            ]);

            return [
                'success' => true,
                'workflows_created' => count($newWorkflows),
                'workflows_adjusted' => $currentWorkflows->count(),
                'adaptations' => $this->getAdaptationSummary($behaviorAnalysis),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to adapt workflow for transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to adapt workflow for transaction.',
            ];
        }
    }

    public function createDynamicReminders(Transaction $transaction, array $behaviorAnalysis): array
    {
        try {
            $client = $transaction->client;
            $reminders = [];

            // Base reminder frequency on engagement level
            $baseFrequency = $this->getBaseReminderFrequency($behaviorAnalysis['engagement_level']);
            
            // Adjust based on response time
            $adjustedFrequency = $this->adjustFrequencyForResponseTime($baseFrequency, $behaviorAnalysis['response_time']);
            
            // Create reminders for different transaction stages
            $stages = $this->getTransactionStages($transaction);
            
            foreach ($stages as $stage) {
                $reminderData = [
                    'type' => 'stage_reminder',
                    'stage' => $stage,
                    'frequency' => $adjustedFrequency,
                    'communication_method' => $behaviorAnalysis['preferred_communication'],
                    'timing' => $behaviorAnalysis['preferred_timing'],
                ];

                $reminder = $this->workflowService->createWorkflow(
                    $transaction,
                    'adaptive_reminder',
                    $reminderData,
                    $this->calculateNextReminderTime($transaction, $stage, $adjustedFrequency)
                );

                if ($reminder) {
                    $reminders[] = $reminder;
                }
            }

            Log::info('Dynamic reminders created', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'reminders_created' => count($reminders),
                'base_frequency' => $baseFrequency,
                'adjusted_frequency' => $adjustedFrequency,
            ]);

            return [
                'success' => true,
                'reminders_created' => count($reminders),
                'frequency' => $adjustedFrequency,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create dynamic reminders', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create dynamic reminders.',
            ];
        }
    }

    public function optimizeCommunicationTiming(Client $client, array $behaviorAnalysis): array
    {
        try {
            $optimalTiming = $this->calculateOptimalTiming($behaviorAnalysis);
            $communicationWindows = $this->getCommunicationWindows($client, $optimalTiming);

            // Update client preferences
            $this->updateClientPreferences($client, [
                'optimal_communication_times' => $communicationWindows,
                'last_analysis_date' => now()->toISOString(),
            ]);

            Log::info('Communication timing optimized', [
                'client_id' => $client->id,
                'optimal_timing' => $optimalTiming,
                'communication_windows' => $communicationWindows,
            ]);

            return [
                'success' => true,
                'optimal_timing' => $optimalTiming,
                'communication_windows' => $communicationWindows,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to optimize communication timing', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to optimize communication timing.',
            ];
        }
    }

    public function predictTransactionOutcome(Transaction $transaction, array $behaviorAnalysis): array
    {
        try {
            $client = $transaction->client;
            $riskFactors = $behaviorAnalysis['risk_factors'];
            $engagementLevel = $behaviorAnalysis['engagement_level'];
            $transactionVelocity = $behaviorAnalysis['transaction_velocity'];

            // Calculate success probability
            $baseSuccessRate = 0.75; // Base success rate
            $engagementModifier = $this->getEngagementModifier($engagementLevel);
            $riskModifier = $this->getRiskModifier($riskFactors);
            $velocityModifier = $this->getVelocityModifier($transactionVelocity);

            $successProbability = $baseSuccessRate * $engagementModifier * $riskModifier * $velocityModifier;
            $successProbability = max(0.1, min(0.95, $successProbability)); // Clamp between 10% and 95%

            // Predict timeline
            $estimatedCompletion = $this->estimateCompletionTime($transaction, $behaviorAnalysis);

            // Generate recommendations
            $recommendations = $this->generateOutcomeRecommendations($successProbability, $riskFactors, $engagementLevel);

            Log::info('Transaction outcome predicted', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'success_probability' => $successProbability,
                'estimated_completion' => $estimatedCompletion,
                'risk_factors_count' => count($riskFactors),
            ]);

            return [
                'success_probability' => round($successProbability * 100, 1),
                'estimated_completion_days' => $estimatedCompletion,
                'risk_level' => $this->calculateRiskLevel($riskFactors),
                'recommendations' => $recommendations,
                'confidence' => $this->calculatePredictionConfidence($behaviorAnalysis),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to predict transaction outcome', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success_probability' => 50,
                'estimated_completion_days' => 30,
                'risk_level' => 'medium',
                'recommendations' => ['Monitor transaction progress closely'],
                'confidence' => 0.5,
            ];
        }
    }

    protected function getClientEngagementData(Client $client): array
    {
        $engagements = ClientTransactionEngagement::where('client_id', $client->id)->get();
        
        return [
            'total_interactions' => $engagements->sum('login_count'),
            'average_response_time' => $engagements->avg('response_time_minutes'),
            'last_active' => $engagements->max('last_active'),
            'engagement_scores' => $engagements->pluck('engagement_level')->toArray(),
        ];
    }

    protected function getTransactionHistory(Client $client): array
    {
        $transactions = Transaction::where('client_id', $client->id)->get();
        
        return [
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $transactions->where('status', 'finalized')->count(),
            'cancelled_transactions' => $transactions->where('status', 'cancelled')->count(),
            'average_duration_days' => $transactions->where('status', 'finalized')
                ->avg(fn($t) => $t->inquiry_date->diffInDays($t->finalized_date)),
        ];
    }

    protected function analyzeResponsePatterns(Client $client): array
    {
        // Analyze response times from engagement data
        $engagements = ClientTransactionEngagement::where('client_id', $client->id)->get();
        
        return [
            'average_response_time' => $engagements->avg('response_time_minutes') ?? 1440, // Default 24 hours
            'response_consistency' => $this->calculateResponseConsistency($engagements),
            'peak_activity_hours' => $this->getPeakActivityHours($engagements),
        ];
    }

    protected function analyzePreferences(Client $client): array
    {
        // This would typically come from user preferences or analysis of past interactions
        return [
            'communication_method' => 'email', // Default
            'communication_timing' => 'business_hours',
            'notification_frequency' => 'moderate',
            'preferred_language' => 'en',
        ];
    }

    protected function calculateEngagementLevel(array $engagementData): string
    {
        $totalInteractions = $engagementData['total_interactions'];
        $averageResponseTime = $engagementData['average_response_time'] ?? 1440;
        
        if ($totalInteractions > 50 && $averageResponseTime < 240) { // 4 hours
            return 'high';
        } elseif ($totalInteractions > 20 && $averageResponseTime < 720) { // 12 hours
            return 'medium';
        } else {
            return 'low';
        }
    }

    protected function calculateTransactionVelocity(array $transactionHistory): string
    {
        $averageDuration = $transactionHistory['average_duration_days'] ?? 30;
        
        if ($averageDuration < 15) {
            return 'fast';
        } elseif ($averageDuration < 45) {
            return 'normal';
        } else {
            return 'slow';
        }
    }

    protected function identifyRiskFactors(array $engagementData, array $transactionHistory): array
    {
        $riskFactors = [];
        
        if ($engagementData['average_response_time'] > 1440) { // 24 hours
            $riskFactors[] = 'slow_response_time';
        }
        
        if ($transactionHistory['cancelled_transactions'] > $transactionHistory['completed_transactions']) {
            $riskFactors[] = 'high_cancellation_rate';
        }
        
        if ($engagementData['last_active'] && $engagementData['last_active']->diffInDays(now()) > 7) {
            $riskFactors[] = 'inactive_recently';
        }
        
        return $riskFactors;
    }

    protected function generateRecommendations(array $engagementData, array $responsePatterns, array $preferences): array
    {
        $recommendations = [];
        
        if ($engagementData['average_response_time'] > 720) { // 12 hours
            $recommendations[] = 'Consider more frequent follow-ups';
        }
        
        if (count($engagementData['engagement_scores']) > 0 && array_count_values($engagementData['engagement_scores'])['low'] > 2) {
            $recommendations[] = 'Implement engagement improvement strategies';
        }
        
        return $recommendations;
    }

    protected function getBaseReminderFrequency(string $engagementLevel): int
    {
        return match ($engagementLevel) {
            'high' => 1, // Daily
            'medium' => 2, // Every 2 days
            'low' => 3, // Every 3 days
            default => 2,
        };
    }

    protected function adjustFrequencyForResponseTime(int $baseFrequency, int $responseTime): int
    {
        if ($responseTime > 1440) { // More than 24 hours
            return min($baseFrequency + 1, 7); // Increase frequency, max weekly
        } elseif ($responseTime < 240) { // Less than 4 hours
            return max($baseFrequency - 1, 1); // Decrease frequency, min daily
        }
        
        return $baseFrequency;
    }

    protected function getTransactionStages(Transaction $transaction): array
    {
        $stages = [
            'inquiry', 'initial_contact', 'property_viewing', 'offer_made',
            'negotiation', 'offer_accepted', 'contract_signed', 'due_diligence',
            'financing', 'closing_preparation', 'finalized'
        ];
        
        $currentStageIndex = array_search($transaction->status, $stages);
        return array_slice($stages, $currentStageIndex + 1);
    }

    protected function calculateNextReminderTime(Transaction $transaction, string $stage, int $frequencyDays): Carbon
    {
        $baseTime = now()->addDays($frequencyDays);
        
        // Adjust based on transaction urgency and client preferences
        if ($transaction->client_action_deadline) {
            $deadline = Carbon::parse($transaction->client_action_deadline);
            if ($deadline->isBefore($baseTime)) {
                return $deadline->subHours(24);
            }
        }
        
        return $baseTime;
    }

    protected function getEngagementModifier(string $engagementLevel): float
    {
        return match ($engagementLevel) {
            'high' => 1.2,
            'medium' => 1.0,
            'low' => 0.8,
            default => 1.0,
        };
    }

    protected function getRiskModifier(array $riskFactors): float
    {
        $riskCount = count($riskFactors);
        return max(0.5, 1.0 - ($riskCount * 0.1));
    }

    protected function getVelocityModifier(string $velocity): float
    {
        return match ($velocity) {
            'fast' => 1.1,
            'normal' => 1.0,
            'slow' => 0.9,
            default => 1.0,
        };
    }

    protected function estimateCompletionTime(Transaction $transaction, array $behaviorAnalysis): int
    {
        $baseTime = 30; // Base 30 days
        $engagementModifier = $behaviorAnalysis['engagement_level'] === 'high' ? 0.8 : 1.2;
        $velocityModifier = $behaviorAnalysis['transaction_velocity'] === 'fast' ? 0.7 : 1.3;
        
        return (int) ($baseTime * $engagementModifier * $velocityModifier);
    }

    protected function calculateRiskLevel(array $riskFactors): string
    {
        $riskCount = count($riskFactors);
        
        if ($riskCount >= 3) {
            return 'high';
        } elseif ($riskCount >= 1) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    protected function calculatePredictionConfidence(array $behaviorAnalysis): float
    {
        // Confidence based on amount of data available
        $dataPoints = count($behaviorAnalysis['recommendations']) + 1;
        return min(0.9, 0.5 + ($dataPoints * 0.1));
    }

    protected function generateOutcomeRecommendations(float $successProbability, array $riskFactors, string $engagementLevel): array
    {
        $recommendations = [];
        
        if ($successProbability < 60) {
            $recommendations[] = 'Increase client engagement and communication frequency';
        }
        
        if (in_array('slow_response_time', $riskFactors)) {
            $recommendations[] = 'Implement automated reminders and follow-ups';
        }
        
        if ($engagementLevel === 'low') {
            $recommendations[] = 'Consider personal outreach and relationship building';
        }
        
        return $recommendations;
    }

    protected function removeInappropriateWorkflows($workflows, array $behaviorAnalysis): void
    {
        foreach ($workflows as $workflow) {
            if ($this->shouldRemoveWorkflow($workflow, $behaviorAnalysis)) {
                $workflow->update(['status' => 'cancelled']);
            }
        }
    }

    protected function createAdaptiveWorkflows(Transaction $transaction, array $behaviorAnalysis): array
    {
        $workflows = [];
        
        // Create workflows based on client behavior
        if ($behaviorAnalysis['engagement_level'] === 'low') {
            $workflows[] = $this->workflowService->createWorkflow(
                $transaction,
                'engagement_boost',
                ['type' => 'engagement_boost', 'method' => 'personal_outreach'],
                now()->addHours(24)
            );
        }
        
        return $workflows;
    }

    protected function adjustExistingWorkflows($workflows, array $behaviorAnalysis): void
    {
        foreach ($workflows as $workflow) {
            $this->adjustWorkflowTiming($workflow, $behaviorAnalysis);
        }
    }

    protected function shouldRemoveWorkflow($workflow, array $behaviorAnalysis): bool
    {
        // Remove workflows that don't match client preferences
        return false; // Simplified for now
    }

    protected function adjustWorkflowTiming($workflow, array $behaviorAnalysis): void
    {
        // Adjust workflow timing based on client behavior
        // This would typically update the scheduled_at time
    }

    protected function getAdaptationSummary(array $behaviorAnalysis): array
    {
        return [
            'engagement_level' => $behaviorAnalysis['engagement_level'],
            'communication_method' => $behaviorAnalysis['preferred_communication'],
            'frequency_adjustment' => $this->getFrequencyAdjustment($behaviorAnalysis),
        ];
    }

    protected function getFrequencyAdjustment(array $behaviorAnalysis): string
    {
        if ($behaviorAnalysis['engagement_level'] === 'high') {
            return 'increased';
        } elseif ($behaviorAnalysis['engagement_level'] === 'low') {
            return 'decreased';
        }
        return 'maintained';
    }

    protected function calculateOptimalTiming(array $behaviorAnalysis): array
    {
        // Calculate optimal communication timing based on behavior analysis
        return [
            'best_hours' => [9, 10, 14, 15], // 9-10 AM, 2-3 PM
            'best_days' => ['monday', 'tuesday', 'wednesday', 'thursday'],
            'avoid_hours' => [12, 13, 18, 19], // Lunch and evening
        ];
    }

    protected function getCommunicationWindows(Client $client, array $optimalTiming): array
    {
        return [
            'morning_window' => '9:00-11:00',
            'afternoon_window' => '14:00-16:00',
            'preferred_days' => $optimalTiming['best_days'],
        ];
    }

    protected function updateClientPreferences(Client $client, array $preferences): void
    {
        // Update client preferences in database
        // This would typically update a preferences table or JSON field
    }

    protected function calculateResponseConsistency($engagements): float
    {
        if ($engagements->count() < 2) {
            return 0.5; // Default consistency
        }
        
        $responseTimes = $engagements->pluck('response_time_minutes')->filter();
        if ($responseTimes->count() < 2) {
            return 0.5;
        }
        
        $mean = $responseTimes->avg();
        $variance = $responseTimes->map(fn($x) => pow($x - $mean, 2))->avg();
        $stdDev = sqrt($variance);
        
        // Lower standard deviation = higher consistency
        return max(0, 1 - ($stdDev / $mean));
    }

    protected function getPeakActivityHours($engagements): array
    {
        // Analyze engagement data to find peak activity hours
        // This is simplified - in reality, you'd analyze timestamps
        return [9, 10, 14, 15]; // Default peak hours
    }
}
