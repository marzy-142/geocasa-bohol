<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    /**
     * Display the broker leaderboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all-time');
        
        $brokers = $this->getBrokerLeaderboard($period);
        $platformStats = $this->getPlatformStats($period);
        
        return Inertia::render('Leaderboard/Index', [
            'brokers' => $brokers,
            'platformStats' => $platformStats,
            'period' => $period
        ]);
    }

    /**
     * Display individual broker details
     */
    public function show(User $broker, Request $request)
    {
        if ($broker->role !== 'broker' || !$broker->is_approved) {
            abort(404);
        }

        $period = $request->get('period', 'all-time');
        
        $brokerStats = $this->getBrokerStats($broker, $period);
        $recentTransactions = $this->getRecentTransactions($broker);
        $activeProperties = $this->getActiveProperties($broker);
        $performanceTrend = $this->getPerformanceTrend($broker);
        
        return Inertia::render('Leaderboard/BrokerDetails', [
            'broker' => $broker,
            'stats' => $brokerStats,
            'recentTransactions' => $recentTransactions,
            'activeProperties' => $activeProperties,
            'performanceTrend' => $performanceTrend,
            'period' => $period
        ]);
    }

    /**
     * Get top brokers for home page display
     */
    public function getTopBrokersForHome($limit = 5)
    {
        return $this->getBrokerLeaderboard('all-time', $limit);
    }

    /**
     * Get broker leaderboard data
     */
    private function getBrokerLeaderboard($period = 'all-time', $limit = null)
    {
        $query = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'transactions as total_sales' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                },
                'properties as active_listings' => function ($query) {
                    $query->where('status', 'available');
                },
                'properties as properties_sold' => function ($query) use ($period) {
                    $query->where('status', 'sold');
                    $this->applyPeriodFilter($query, $period);
                }
            ])
            ->withSum([
                'transactions as total_commission' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                }
            ], 'commission_amount')
            ->withSum([
                'transactions as total_sales_value' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                }
            ], 'final_price');

        // Calculate success rate
        $brokers = $query->get()->map(function ($broker) use ($period) {
            $totalInquiries = $broker->properties()
                ->withCount(['inquiries' => function ($query) use ($period) {
                    $this->applyPeriodFilter($query, $period);
                }])
                ->get()
                ->sum('inquiries_count');

            $broker->success_rate = $totalInquiries > 0 
                ? round(($broker->total_sales / $totalInquiries) * 100, 1)
                : 0;

            $broker->total_commission = $broker->total_commission ?? 0;
            $broker->total_sales_value = $broker->total_sales_value ?? 0;

            return $broker;
        });

        // Sort by total sales value (descending)
        $brokers = $brokers->sortByDesc('total_sales_value');

        if ($limit) {
            $brokers = $brokers->take($limit);
        }

        return $brokers->values();
    }

    /**
     * Get platform statistics
     */
    private function getPlatformStats($period = 'all-time')
    {
        $transactionQuery = Transaction::where('status', 'finalized');
        $propertyQuery = Property::query();
        
        $this->applyPeriodFilter($transactionQuery, $period);
        $this->applyPeriodFilter($propertyQuery, $period);

        return [
            'total_sales' => $transactionQuery->count(),
            'total_commission' => $transactionQuery->sum('commission_amount') ?? 0,
            'total_sales_value' => $transactionQuery->sum('final_price') ?? 0,
            'active_brokers' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->whereHas('transactions', function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                })
                ->count(),
            'properties_sold' => $propertyQuery->where('status', 'sold')->count(),
            'active_listings' => Property::where('status', 'available')->count()
        ];
    }

    /**
     * Get individual broker statistics
     */
    private function getBrokerStats(User $broker, $period = 'all-time')
    {
        $transactionQuery = $broker->transactions()->where('status', 'finalized');
        $propertyQuery = $broker->properties();
        
        $this->applyPeriodFilter($transactionQuery, $period);
        $this->applyPeriodFilter($propertyQuery, $period);

        $totalSales = $transactionQuery->count();
        $totalInquiries = $broker->properties()
            ->withCount(['inquiries' => function ($query) use ($period) {
                $this->applyPeriodFilter($query, $period);
            }])
            ->get()
            ->sum('inquiries_count');

        return [
            'total_sales' => $totalSales,
            'total_commission' => $transactionQuery->sum('commission_amount') ?? 0,
            'total_sales_value' => $transactionQuery->sum('final_price') ?? 0,
            'properties_sold' => $propertyQuery->where('status', 'sold')->count(),
            'active_listings' => $broker->properties()->where('status', 'available')->count(),
            'total_clients' => $broker->clients()->count(),
            'success_rate' => $totalInquiries > 0 ? round(($totalSales / $totalInquiries) * 100, 1) : 0,
            'avg_days_to_close' => $this->getAverageDaysToClose($broker, $period)
        ];
    }

    /**
     * Get recent transactions for a broker
     */
    private function getRecentTransactions(User $broker, $limit = 10)
    {
        return $broker->transactions()
            ->with(['property', 'client'])
            ->where('status', 'finalized')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active properties for a broker
     */
    private function getActiveProperties(User $broker, $limit = 10)
    {
        return $broker->properties()
            ->where('status', 'available')
            ->withCount('inquiries')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get 12-month performance trend for a broker
     */
    private function getPerformanceTrend(User $broker)
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $sales = $broker->transactions()
                ->where('status', 'finalized')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count();
                
            $commission = $broker->transactions()
                ->where('status', 'finalized')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->sum('commission_amount') ?? 0;

            $months[] = [
                'month' => $date->format('M Y'),
                'sales' => $sales,
                'commission' => $commission
            ];
        }

        return $months;
    }

    /**
     * Get average days to close deals for a broker
     */
    private function getAverageDaysToClose(User $broker, $period = 'all-time')
    {
        $query = $broker->transactions()
            ->where('status', 'finalized')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at');
            
        $this->applyPeriodFilter($query, $period);
        
        $transactions = $query->get();
        
        if ($transactions->isEmpty()) {
            return 0;
        }

        $totalDays = $transactions->sum(function ($transaction) {
            return $transaction->created_at->diffInDays($transaction->updated_at);
        });

        return round($totalDays / $transactions->count(), 1);
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, $period)
    {
        switch ($period) {
            case 'this-year':
                $query->whereYear('created_at', now()->year);
                break;
            case 'this-month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'last-30-days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'last-90-days':
                $query->where('created_at', '>=', now()->subDays(90));
                break;
            // 'all-time' - no filter needed
        }
    }
}