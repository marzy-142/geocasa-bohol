<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrokerAnalyticsController extends Controller
{
    /**
     * Display broker analytics dashboard
     */
    public function index(Request $request)
    {
        $timeRange = $request->get('time_range', '30'); // days
        $brokerId = $request->get('broker_id');
        
        // Get all approved brokers for dropdown
        $brokers = User::approvedBrokers()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Calculate overall statistics
        $overallStats = $this->getOverallStats($timeRange, $brokerId);
        
        // Get top performing brokers
        $topBrokers = $this->getTopPerformingBrokers($timeRange);
        
        // Get broker performance trends
        $performanceTrends = $this->getPerformanceTrends($timeRange, $brokerId);
        
        // Get property performance analytics
        $propertyAnalytics = $this->getPropertyAnalytics($timeRange, $brokerId);
        
        // Get client analytics
        $clientAnalytics = $this->getClientAnalytics($timeRange, $brokerId);

        return Inertia::render('Admin/Analytics/BrokerDashboard', [
            'overallStats' => $overallStats,
            'topBrokers' => $topBrokers,
            'performanceTrends' => $performanceTrends,
            'propertyAnalytics' => $propertyAnalytics,
            'clientAnalytics' => $clientAnalytics,
            'brokers' => $brokers,
            'filters' => [
                'time_range' => $timeRange,
                'broker_id' => $brokerId,
            ],
        ]);
    }

    /**
     * Display broker analytics page (alternative route)
     */
    public function analyticsPage(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Get individual broker analytics
     */
    public function show(User $broker, Request $request)
    {
        $timeRange = $request->get('time_range', '30');
        
        // Verify broker is approved
        if (!$broker->is_approved || $broker->application_status !== 'approved') {
            abort(404, 'Broker not found or not approved');
        }

        // Get broker performance metrics
        $performanceMetrics = $this->getBrokerPerformanceMetrics($broker, $timeRange);
        
        // Get broker's properties analytics
        $propertyAnalytics = $this->getBrokerPropertyAnalytics($broker, $timeRange);
        
        // Get broker's client analytics
        $clientAnalytics = $this->getBrokerClientAnalytics($broker, $timeRange);
        
        // Get broker's activity timeline
        $activityTimeline = $this->getBrokerActivityTimeline($broker, $timeRange);

        return Inertia::render('Admin/Analytics/BrokerDetail', [
            'broker' => $broker,
            'performanceMetrics' => $performanceMetrics,
            'propertyAnalytics' => $propertyAnalytics,
            'clientAnalytics' => $clientAnalytics,
            'activityTimeline' => $activityTimeline,
            'filters' => [
                'time_range' => $timeRange,
            ],
        ]);
    }

    /**
     * Get overall statistics
     */
    private function getOverallStats($timeRange, $brokerId = null)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $query = User::approvedBrokers();

        if ($brokerId) {
            $query->where('id', $brokerId);
        }

        $brokers = $query->get();

        return [
            'total_brokers' => $brokers->count(),
            'active_brokers' => $brokers->whereNull('suspended_at')->count(),
            'total_properties' => Property::whereIn('broker_id', $brokers->pluck('id'))
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_transactions' => Transaction::whereIn('broker_id', $brokers->pluck('id'))
                ->where('created_at', '>=', $startDate)
                ->count(),
            'avg_response_time' => $this->calculateAverageResponseTime($brokers, $timeRange),
            'conversion_rate' => $this->calculateConversionRate($brokers, $timeRange),
        ];
    }

    /**
     * Get top performing brokers
     */
    private function getTopPerformingBrokers($timeRange)
    {
        $startDate = Carbon::now()->subDays($timeRange);

        return User::approvedBrokers()
            ->withCount([
                'properties as recent_properties_count' => function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                },
                'transactions as recent_transactions_count' => function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                },
            ])
            ->orderByDesc('recent_transactions_count')
            ->limit(10)
            ->get()
            ->map(function ($broker) {
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'properties_count' => $broker->recent_properties_count,
                    'transactions_count' => $broker->recent_transactions_count,
                    'performance_score' => $this->calculatePerformanceScore($broker),
                ];
            });
    }

    /**
     * Get performance trends
     */
    private function getPerformanceTrends($timeRange, $brokerId = null)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $query = User::approvedBrokers();

        if ($brokerId) {
            $query->where('id', $brokerId);
        }

        $brokers = $query->pluck('id');

        // Get daily trends for the last 30 days
        $trends = [];
        for ($i = $timeRange; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $nextDate = $date->copy()->addDay();

            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'properties' => Property::whereIn('broker_id', $brokers)
                    ->whereBetween('created_at', [$date, $nextDate])
                    ->count(),
                'transactions' => Transaction::whereIn('broker_id', $brokers)
                    ->whereBetween('created_at', [$date, $nextDate])
                    ->count(),
            ];
        }

        return $trends;
    }


    /**
     * Get property analytics
     */
    private function getPropertyAnalytics($timeRange, $brokerId = null)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $query = Property::where('created_at', '>=', $startDate);

        if ($brokerId) {
            $query->where('broker_id', $brokerId);
        }

        $properties = $query->get();

        return [
            'total_properties' => $properties->count(),
            'properties_by_type' => $properties->groupBy('type')->map->count(),
            'properties_by_status' => $properties->groupBy('status')->map->count(),
            'avg_price' => $properties->avg('total_price'),
            'price_range_distribution' => $this->getPriceRangeDistribution($properties),
        ];
    }

    /**
     * Get client analytics
     */
    private function getClientAnalytics($timeRange, $brokerId = null)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $query = User::where('role', 'client')
            ->where('created_at', '>=', $startDate);

        if ($brokerId) {
            // Get clients assigned to this broker
            $query->whereHas('assignedBroker', function ($q) use ($brokerId) {
                $q->where('broker_id', $brokerId);
            });
        }

        $clients = $query->get();

        return [
            'total_clients' => $clients->count(),
            'new_clients' => $clients->where('created_at', '>=', $startDate)->count(),
            'client_acquisition_rate' => $this->calculateClientAcquisitionRate($clients, $timeRange),
            'client_retention_rate' => $this->calculateClientRetentionRate($clients, $timeRange),
        ];
    }

    /**
     * Get broker performance metrics
     */
    private function getBrokerPerformanceMetrics($broker, $timeRange)
    {
        $startDate = Carbon::now()->subDays($timeRange);

        return [
            'properties_listed' => $broker->properties()->where('created_at', '>=', $startDate)->count(),
            'properties_sold' => $broker->properties()->where('status', 'sold')->where('created_at', '>=', $startDate)->count(),
            'avg_response_time' => $this->calculateBrokerResponseTime($broker, $timeRange),
            'client_satisfaction' => $this->calculateClientSatisfaction($broker, $timeRange),
            'conversion_rate' => $this->calculateBrokerConversionRate($broker, $timeRange),
        ];
    }

    /**
     * Get broker property analytics
     */
    private function getBrokerPropertyAnalytics($broker, $timeRange)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $properties = $broker->properties()->where('created_at', '>=', $startDate)->get();

        return [
            'total' => $properties->count(),
            'by_type' => $properties->groupBy('type')->map->count(),
            'by_status' => $properties->groupBy('status')->map->count(),
            'avg_price' => $properties->avg('total_price'),
            'total_value' => $properties->sum('total_price'),
        ];
    }

    /**
     * Get broker client analytics
     */
    private function getBrokerClientAnalytics($broker, $timeRange)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        $clients = $broker->clients()->where('created_at', '>=', $startDate)->get();

        return [
            'total' => $clients->count(),
            'new_clients' => $clients->where('created_at', '>=', $startDate)->count(),
            'active_clients' => $clients->where('status', 'active')->count(),
        ];
    }


    /**
     * Get broker activity timeline
     */
    private function getBrokerActivityTimeline($broker, $timeRange)
    {
        $startDate = Carbon::now()->subDays($timeRange);
        
        $activities = collect();

        // Add property activities
        $broker->properties()->where('created_at', '>=', $startDate)->get()->each(function ($property) use ($activities) {
            $activities->push([
                'type' => 'property_listed',
                'description' => "Listed property: {$property->title}",
                'date' => $property->created_at,
                'icon' => 'home',
            ]);
        });

        // Add transaction activities
        $broker->transactions()->where('created_at', '>=', $startDate)->get()->each(function ($transaction) use ($activities) {
            $activities->push([
                'type' => 'transaction_completed',
                'description' => "Completed transaction: ₱" . number_format($transaction->total_amount, 2),
                'date' => $transaction->created_at,
                'icon' => 'currency-dollar',
            ]);
        });

        return $activities->sortByDesc('date')->values();
    }

    // Helper methods
    private function calculateAverageResponseTime($brokers, $timeRange)
    {
        // Calculate actual response time from inquiries
        $startDate = Carbon::now()->subDays($timeRange);
        $inquiries = Inquiry::whereIn('assigned_broker_id', $brokers->pluck('id'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('responded_at')
            ->get();

        if ($inquiries->isEmpty()) {
            return 0; // No inquiries to calculate from
        }

        $totalHours = $inquiries->sum(function ($inquiry) {
            return $inquiry->created_at->diffInHours($inquiry->responded_at);
        });

        return round($totalHours / $inquiries->count(), 1);
    }

    private function calculateConversionRate($brokers, $timeRange)
    {
        // Calculate actual conversion rate from inquiries to transactions
        $startDate = Carbon::now()->subDays($timeRange);
        $brokerIds = $brokers->pluck('id');
        
        $totalInquiries = Inquiry::whereIn('assigned_broker_id', $brokerIds)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        $totalTransactions = Transaction::whereIn('broker_id', $brokerIds)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($totalInquiries === 0) {
            return 0; // No inquiries to convert
        }

        return round(($totalTransactions / $totalInquiries) * 100, 1);
    }

    private function calculatePerformanceScore($broker)
    {
        $score = 0;
        $score += ($broker->recent_properties_count ?? 0) * 2;
        $score += ($broker->recent_transactions_count ?? 0) * 5;
        return min(100, max(0, $score));
    }


    private function getPriceRangeDistribution($properties)
    {
        $ranges = [
            '0-1M' => 0,
            '1M-5M' => 0,
            '5M-10M' => 0,
            '10M+' => 0,
        ];

        $properties->each(function ($property) use (&$ranges) {
            $price = $property->total_price;
            if ($price <= 1000000) {
                $ranges['0-1M']++;
            } elseif ($price <= 5000000) {
                $ranges['1M-5M']++;
            } elseif ($price <= 10000000) {
                $ranges['5M-10M']++;
            } else {
                $ranges['10M+']++;
            }
        });

        return $ranges;
    }

    private function calculateClientAcquisitionRate($clients, $timeRange)
    {
        // Calculate based on new clients vs total clients
        $totalClients = $clients->count();
        $newClients = $clients->where('created_at', '>=', Carbon::now()->subDays($timeRange))->count();
        
        if ($totalClients === 0) {
            return 0;
        }
        
        return round(($newClients / $totalClients) * 100, 1);
    }

    private function calculateClientRetentionRate($clients, $timeRange)
    {
        // Calculate based on active clients vs total clients
        $totalClients = $clients->count();
        $activeClients = $clients->where('status', 'active')->count();
        
        if ($totalClients === 0) {
            return 0;
        }
        
        return round(($activeClients / $totalClients) * 100, 1);
    }

    private function calculateBrokerResponseTime($broker, $timeRange)
    {
        // Calculate actual response time for this broker
        $startDate = Carbon::now()->subDays($timeRange);
        $inquiries = Inquiry::where('assigned_broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('responded_at')
            ->get();

        if ($inquiries->isEmpty()) {
            return 0;
        }

        $totalHours = $inquiries->sum(function ($inquiry) {
            return $inquiry->created_at->diffInHours($inquiry->responded_at);
        });

        return round($totalHours / $inquiries->count(), 1);
    }

    private function calculateClientSatisfaction($broker, $timeRange)
    {
        // For now, return a realistic range based on broker performance
        $propertiesCount = $broker->properties()->count();
        $transactionsCount = $broker->transactions()->where('status', 'finalized')->count();
        
        // Base satisfaction on activity level
        $baseScore = 3.0;
        $activityBonus = min(1.5, ($propertiesCount * 0.1) + ($transactionsCount * 0.2));
        
        return round($baseScore + $activityBonus, 1);
    }

    private function calculateBrokerConversionRate($broker, $timeRange)
    {
        // Calculate actual conversion rate for this broker
        $startDate = Carbon::now()->subDays($timeRange);
        
        $totalInquiries = Inquiry::where('assigned_broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        $totalTransactions = Transaction::where('broker_id', $broker->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($totalInquiries === 0) {
            return 0;
        }

        return round(($totalTransactions / $totalInquiries) * 100, 1);
    }
}
