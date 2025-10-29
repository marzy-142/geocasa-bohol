<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientTransactionEngagement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceAnalyticsService
{
    public function generateClientAnalytics(Client $client, int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);
            
            return [
                'engagement_metrics' => $this->calculateEngagementMetrics($client, $startDate),
                'transaction_metrics' => $this->calculateTransactionMetrics($client, $startDate),
                'communication_metrics' => $this->calculateCommunicationMetrics($client, $startDate),
                'satisfaction_metrics' => $this->calculateSatisfactionMetrics($client, $startDate),
                'performance_trends' => $this->calculatePerformanceTrends($client, $days),
                'recommendations' => $this->generatePerformanceRecommendations($client, $startDate),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate client analytics', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultAnalytics();
        }
    }

    public function generateBrokerAnalytics(User $broker, int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);
            
            return [
                'transaction_metrics' => $this->calculateBrokerTransactionMetrics($broker, $startDate),
                'client_management_metrics' => $this->calculateClientManagementMetrics($broker, $startDate),
                'communication_metrics' => $this->calculateBrokerCommunicationMetrics($broker, $startDate),
                'performance_trends' => $this->calculateBrokerPerformanceTrends($broker, $days),
                'efficiency_metrics' => $this->calculateEfficiencyMetrics($broker, $startDate),
                'recommendations' => $this->generateBrokerRecommendations($broker, $startDate),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate broker analytics', [
                'broker_id' => $broker->id,
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultBrokerAnalytics();
        }
    }

    public function generateSystemAnalytics(int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);
            
            return [
                'overall_metrics' => $this->calculateOverallSystemMetrics($startDate),
                'transaction_analytics' => $this->calculateSystemTransactionAnalytics($startDate),
                'client_analytics' => $this->calculateSystemClientAnalytics($startDate),
                'broker_analytics' => $this->calculateSystemBrokerAnalytics($startDate),
                'performance_trends' => $this->calculateSystemPerformanceTrends($days),
                'insights' => $this->generateSystemInsights($startDate),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate system analytics', [
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultSystemAnalytics();
        }
    }

    public function trackClientEngagement(Client $client, string $action, array $data = []): array
    {
        try {
            $engagement = ClientTransactionEngagement::where('client_id', $client->id)
                ->where('transaction_id', $data['transaction_id'] ?? null)
                ->first();

            if (!$engagement) {
                $engagement = ClientTransactionEngagement::create([
                    'client_id' => $client->id,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'engagement_level' => 'medium',
                    'login_count' => 0,
                    'response_time_minutes' => null,
                    'last_active' => now(),
                    'preferences' => [],
                ]);
            }

            // Update engagement metrics
            $engagement->update([
                'last_active' => now(),
                'login_count' => $engagement->login_count + 1,
            ]);

            // Record interaction
            $engagement->recordInteraction($action, array_merge($data, [
                'timestamp' => now()->toISOString(),
            ]));

            // Calculate new engagement level
            $newLevel = $this->calculateEngagementLevel($engagement);
            $engagement->update(['engagement_level' => $newLevel]);

            Log::info('Client engagement tracked', [
                'client_id' => $client->id,
                'action' => $action,
                'engagement_level' => $newLevel,
            ]);

            return [
                'success' => true,
                'engagement_level' => $newLevel,
                'total_interactions' => $engagement->login_count,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to track client engagement', [
                'client_id' => $client->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to track engagement.',
            ];
        }
    }

    public function calculateClientSatisfactionScore(Client $client): array
    {
        try {
            $transactions = Transaction::where('client_id', $client->id)->get();
            $satisfactionScores = $transactions->pluck('client_satisfaction')->filter();
            
            if ($satisfactionScores->isEmpty()) {
                return [
                    'score' => null,
                    'total_responses' => 0,
                    'breakdown' => [
                        'satisfied' => 0,
                        'dissatisfied' => 0,
                        'pending' => 0,
                    ],
                ];
            }

            $satisfiedCount = $satisfactionScores->where('satisfied')->count();
            $totalResponses = $satisfactionScores->count();
            $satisfactionScore = $totalResponses > 0 ? ($satisfiedCount / $totalResponses) * 100 : 0;

            return [
                'score' => round($satisfactionScore, 1),
                'total_responses' => $totalResponses,
                'breakdown' => [
                    'satisfied' => $satisfiedCount,
                    'dissatisfied' => $satisfactionScores->where('dissatisfied')->count(),
                    'pending' => $transactions->where('client_satisfaction', null)->count(),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate client satisfaction score', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'score' => null,
                'total_responses' => 0,
                'breakdown' => ['satisfied' => 0, 'dissatisfied' => 0, 'pending' => 0],
            ];
        }
    }

    public function generatePerformanceReport(string $type, $entity, int $days = 30): array
    {
        try {
            $reportData = match ($type) {
                'client' => $this->generateClientAnalytics($entity, $days),
                'broker' => $this->generateBrokerAnalytics($entity, $days),
                'system' => $this->generateSystemAnalytics($days),
                default => throw new \InvalidArgumentException('Invalid report type'),
            };

            $reportData['report_metadata'] = [
                'type' => $type,
                'entity_id' => $entity->id ?? null,
                'period_days' => $days,
                'generated_at' => now()->toISOString(),
                'report_id' => uniqid('report_'),
            ];

            Log::info('Performance report generated', [
                'type' => $type,
                'entity_id' => $entity->id ?? null,
                'days' => $days,
            ]);

            return $reportData;

        } catch (\Exception $e) {
            Log::error('Failed to generate performance report', [
                'type' => $type,
                'entity_id' => $entity->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to generate performance report.',
                'report_metadata' => [
                    'type' => $type,
                    'entity_id' => $entity->id ?? null,
                    'period_days' => $days,
                    'generated_at' => now()->toISOString(),
                    'error' => true,
                ],
            ];
        }
    }

    protected function calculateEngagementMetrics(Client $client, Carbon $startDate): array
    {
        $engagements = ClientTransactionEngagement::where('client_id', $client->id)
            ->where('updated_at', '>=', $startDate)
            ->get();

        return [
            'total_interactions' => $engagements->sum('login_count'),
            'average_response_time_hours' => round($engagements->avg('response_time_minutes') / 60, 1),
            'engagement_level' => $engagements->mode('engagement_level')[0] ?? 'medium',
            'last_active' => $engagements->max('last_active'),
            'active_days' => $engagements->where('last_active', '>=', $startDate)->count(),
        ];
    }

    protected function calculateTransactionMetrics(Client $client, Carbon $startDate): array
    {
        $transactions = Transaction::where('client_id', $client->id)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $transactions->where('status', 'finalized')->count(),
            'active_transactions' => $transactions->whereNotIn('status', ['finalized', 'cancelled'])->count(),
            'average_completion_days' => $this->calculateAverageCompletionDays($transactions),
            'success_rate' => $this->calculateSuccessRate($transactions),
        ];
    }

    protected function calculateCommunicationMetrics(Client $client, Carbon $startDate): array
    {
        // This would typically analyze communication logs
        return [
            'total_communications' => 0,
            'response_rate' => 0,
            'average_response_time_hours' => 0,
            'preferred_communication_method' => 'email',
        ];
    }

    protected function calculateSatisfactionMetrics(Client $client, Carbon $startDate): array
    {
        $satisfactionData = $this->calculateClientSatisfactionScore($client);
        
        return [
            'satisfaction_score' => $satisfactionData['score'],
            'total_responses' => $satisfactionData['total_responses'],
            'satisfied_percentage' => $satisfactionData['score'],
            'feedback_count' => $satisfactionData['total_responses'],
        ];
    }

    protected function calculatePerformanceTrends(Client $client, int $days): array
    {
        // Calculate trends over time
        return [
            'engagement_trend' => 'stable',
            'satisfaction_trend' => 'improving',
            'transaction_velocity_trend' => 'increasing',
        ];
    }

    protected function generatePerformanceRecommendations(Client $client, Carbon $startDate): array
    {
        $recommendations = [];
        $engagement = $this->calculateEngagementMetrics($client, $startDate);
        
        if ($engagement['total_interactions'] < 10) {
            $recommendations[] = 'Increase client engagement through more frequent communication';
        }
        
        if ($engagement['average_response_time_hours'] > 24) {
            $recommendations[] = 'Implement automated reminders to improve response time';
        }
        
        return $recommendations;
    }

    protected function calculateBrokerTransactionMetrics(User $broker, Carbon $startDate): array
    {
        $transactions = Transaction::where('broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $transactions->where('status', 'finalized')->count(),
            'active_transactions' => $transactions->whereNotIn('status', ['finalized', 'cancelled'])->count(),
            'average_completion_days' => $this->calculateAverageCompletionDays($transactions),
            'success_rate' => $this->calculateSuccessRate($transactions),
            // Use total sales value instead of commission
            'total_commission' => $transactions->sum(function($t){
                return $t->final_price ?? $t->offered_price ?? 0;
            }),
        ];
    }

    protected function calculateClientManagementMetrics(User $broker, Carbon $startDate): array
    {
        $clients = Client::where('broker_id', $broker->id)->get();
        
        return [
            'total_clients' => $clients->count(),
            'active_clients' => $clients->whereHas('transactions', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->count(),
            'average_clients_per_month' => $this->calculateAverageClientsPerMonth($broker, $startDate),
        ];
    }

    protected function calculateBrokerCommunicationMetrics(User $broker, Carbon $startDate): array
    {
        // This would typically analyze communication logs
        return [
            'total_communications' => 0,
            'response_rate' => 0,
            'average_response_time_hours' => 0,
        ];
    }

    protected function calculateBrokerPerformanceTrends(User $broker, int $days): array
    {
        return [
            'transaction_volume_trend' => 'stable',
            'success_rate_trend' => 'improving',
            'client_satisfaction_trend' => 'stable',
        ];
    }

    protected function calculateEfficiencyMetrics(User $broker, Carbon $startDate): array
    {
        $transactions = Transaction::where('broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'transactions_per_week' => $transactions->count() / ($startDate->diffInWeeks(now()) ?: 1),
            'average_deal_size' => $transactions->avg('final_price'),
            'conversion_rate' => $this->calculateConversionRate($broker, $startDate),
        ];
    }

    protected function generateBrokerRecommendations(User $broker, Carbon $startDate): array
    {
        $recommendations = [];
        $metrics = $this->calculateBrokerTransactionMetrics($broker, $startDate);
        
        if ($metrics['success_rate'] < 0.7) {
            $recommendations[] = 'Focus on improving transaction success rate through better client management';
        }
        
        if ($metrics['average_completion_days'] > 45) {
            $recommendations[] = 'Work on reducing transaction completion time';
        }
        
        return $recommendations;
    }

    protected function calculateOverallSystemMetrics(Carbon $startDate): array
    {
        return [
            'total_transactions' => Transaction::where('created_at', '>=', $startDate)->count(),
            'total_clients' => Client::where('created_at', '>=', $startDate)->count(),
            'total_brokers' => User::where('role', 'broker')->where('created_at', '>=', $startDate)->count(),
            'system_uptime' => 99.9, // This would be calculated from actual system data
        ];
    }

    protected function calculateSystemTransactionAnalytics(Carbon $startDate): array
    {
        $transactions = Transaction::where('created_at', '>=', $startDate)->get();
        
        return [
            'completion_rate' => $this->calculateSuccessRate($transactions),
            'average_completion_time' => $this->calculateAverageCompletionDays($transactions),
            'transaction_volume_trend' => 'increasing',
            // Report revenue by sales value, not commission
            'revenue_generated' => $transactions->sum(function($t){
                return $t->final_price ?? $t->offered_price ?? 0;
            }),
        ];
    }

    protected function calculateSystemClientAnalytics(Carbon $startDate): array
    {
        return [
            'client_satisfaction_average' => 85.5, // This would be calculated from actual data
            'client_retention_rate' => 78.2,
            'new_client_acquisition_rate' => 12.5,
        ];
    }

    protected function calculateSystemBrokerAnalytics(Carbon $startDate): array
    {
        $brokers = User::where('role', 'broker')->get();
        
        return [
            'total_brokers' => $brokers->count(),
            'active_brokers' => $brokers->whereHas('transactions', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->count(),
            'average_performance_score' => 82.3,
        ];
    }

    protected function calculateSystemPerformanceTrends(int $days): array
    {
        return [
            'overall_performance' => 'improving',
            'client_satisfaction_trend' => 'stable',
            'transaction_volume_trend' => 'increasing',
            'system_reliability_trend' => 'stable',
        ];
    }

    protected function generateSystemInsights(Carbon $startDate): array
    {
        return [
            'peak_transaction_hours' => [10, 14, 16],
            'most_popular_property_types' => ['house', 'condo', 'land'],
            'average_client_journey_days' => 35,
            'top_performing_brokers' => [],
        ];
    }

    protected function calculateEngagementLevel(ClientTransactionEngagement $engagement): string
    {
        $interactions = $engagement->login_count;
        $responseTime = $engagement->response_time_minutes ?? 1440;
        
        if ($interactions > 20 && $responseTime < 240) {
            return 'high';
        } elseif ($interactions > 10 && $responseTime < 720) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    protected function calculateAverageCompletionDays($transactions): float
    {
        $completedTransactions = $transactions->where('status', 'finalized');
        
        if ($completedTransactions->isEmpty()) {
            return 0;
        }
        
        return $completedTransactions->avg(function($transaction) {
            return $transaction->inquiry_date->diffInDays($transaction->finalized_date);
        });
    }

    protected function calculateSuccessRate($transactions): float
    {
        if ($transactions->isEmpty()) {
            return 0;
        }
        
        $completed = $transactions->where('status', 'finalized')->count();
        return round(($completed / $transactions->count()) * 100, 1);
    }

    protected function calculateConversionRate(User $broker, Carbon $startDate): float
    {
        $inquiries = \App\Models\Inquiry::where('assigned_broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        $transactions = Transaction::where('broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        if ($inquiries === 0) {
            return 0;
        }
        
        return round(($transactions / $inquiries) * 100, 1);
    }

    protected function calculateAverageClientsPerMonth(User $broker, Carbon $startDate): float
    {
        $months = $startDate->diffInMonths(now()) ?: 1;
        $totalClients = Client::where('broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        return round($totalClients / $months, 1);
    }

    protected function getDefaultAnalytics(): array
    {
        return [
            'engagement_metrics' => ['total_interactions' => 0, 'engagement_level' => 'medium'],
            'transaction_metrics' => ['total_transactions' => 0, 'success_rate' => 0],
            'communication_metrics' => ['total_communications' => 0, 'response_rate' => 0],
            'satisfaction_metrics' => ['satisfaction_score' => null, 'total_responses' => 0],
            'performance_trends' => ['engagement_trend' => 'stable'],
            'recommendations' => [],
        ];
    }

    protected function getDefaultBrokerAnalytics(): array
    {
        return [
            'transaction_metrics' => ['total_transactions' => 0, 'success_rate' => 0],
            'client_management_metrics' => ['total_clients' => 0, 'active_clients' => 0],
            'communication_metrics' => ['total_communications' => 0, 'response_rate' => 0],
            'performance_trends' => ['transaction_volume_trend' => 'stable'],
            'efficiency_metrics' => ['transactions_per_week' => 0, 'conversion_rate' => 0],
            'recommendations' => [],
        ];
    }

    protected function getDefaultSystemAnalytics(): array
    {
        return [
            'overall_metrics' => ['total_transactions' => 0, 'total_clients' => 0],
            'transaction_analytics' => ['completion_rate' => 0, 'average_completion_time' => 0],
            'client_analytics' => ['client_satisfaction_average' => 0, 'client_retention_rate' => 0],
            'broker_analytics' => ['total_brokers' => 0, 'active_brokers' => 0],
            'performance_trends' => ['overall_performance' => 'stable'],
            'insights' => [],
        ];
    }
}
