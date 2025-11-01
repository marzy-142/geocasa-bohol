<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BrokerRankingService
{
    /**
     * Get top performing brokers with consistent ranking logic
     * 
     * Rankings are based on:
     * 1. Total finalized sales (primary metric)
     * 2. Active listings count (secondary metric)
     * 3. Total sales value (tiebreaker)
     * 
     * @param int $limit Number of brokers to return
     * @param array $options Additional options like time_range, include_inactive, etc.
     * @return Collection
     */
    public function getTopPerformingBrokers($limit = 10, array $options = [])
    {
        $timeRange = $options['time_range'] ?? null;
        $includeInactive = $options['include_inactive'] ?? false;
        
        $query = User::where('role', 'broker')
            ->where('is_approved', true);

        if (!$includeInactive) {
            $query->whereNull('suspended_at');
        }

        // Apply time range filter if specified
        if ($timeRange) {
            $startDate = now()->subDays($timeRange);
            
            $query->withCount([
                'transactions as total_sales' => function ($q) use ($startDate) {
                    $q->where('status', 'finalized')
                      ->where('created_at', '>=', $startDate);
                },
                'properties as active_listings' => function ($q) {
                    $q->where('status', 'available');
                }
            ])
            ->withSum([
                'transactions as total_sales_value' => function ($q) use ($startDate) {
                    $q->where('status', 'finalized')
                      ->where('created_at', '>=', $startDate);
                }
            ], DB::raw('COALESCE(final_price, offered_price)'));
        } else {
            // All-time rankings
            $query->withCount([
                'transactions as total_sales' => function ($q) {
                    $q->where('status', 'finalized');
                },
                'properties as active_listings' => function ($q) {
                    $q->where('status', 'available');
                }
            ])
            ->withSum([
                'transactions as total_sales_value' => function ($q) {
                    $q->where('status', 'finalized');
                }
            ], DB::raw('COALESCE(final_price, offered_price)'));
        }

        return $query->get()
            ->map(function ($broker) {
                $broker->total_sales_value = $broker->total_sales_value ?? 0;
                
                // Add avatar fields for UserAvatar component
                $broker->avatar_url = $broker->avatar 
                    ? asset('storage/' . $broker->avatar) . '?v=' . time() 
                    : null;
                
                // Add computed fields for backward compatibility
                $broker->finalized_transactions_count = $broker->total_sales;
                $broker->total_properties = $broker->total_sales;
                $broker->total_transactions = $broker->total_sales;
                
                return $broker;
            })
            ->sortByDesc(function ($broker) {
                // Multi-level sorting: sales count (primary), active listings (secondary), sales value (tiebreaker)
                return [
                    $broker->total_sales,
                    $broker->active_listings,
                    $broker->total_sales_value
                ];
            })
            ->take($limit)
            ->values();
    }

    /**
     * Get broker statistics for a specific broker
     * 
     * @param int $brokerId
     * @return array|null
     */
    public function getBrokerStats($brokerId)
    {
        $broker = User::where('id', $brokerId)
            ->where('role', 'broker')
            ->withCount([
                'transactions as total_sales' => function ($q) {
                    $q->where('status', 'finalized');
                },
                'properties as active_listings' => function ($q) {
                    $q->where('status', 'available');
                },
                'properties as total_listings',
                'properties as sold_properties' => function ($q) {
                    $q->where('status', 'sold');
                }
            ])
            ->withSum([
                'transactions as total_sales_value' => function ($q) {
                    $q->where('status', 'finalized');
                }
            ], DB::raw('COALESCE(final_price, offered_price)'))
            ->first();

        if (!$broker) {
            return null;
        }

        return [
            'total_sales' => $broker->total_sales ?? 0,
            'active_listings' => $broker->active_listings ?? 0,
            'total_listings' => $broker->total_listings ?? 0,
            'sold_properties' => $broker->sold_properties ?? 0,
            'total_sales_value' => $broker->total_sales_value ?? 0,
        ];
    }

    /**
     * Calculate broker ranking position
     * 
     * @param int $brokerId
     * @return int|null Position (1-based index)
     */
    public function getBrokerRank($brokerId)
    {
        $allBrokers = $this->getTopPerformingBrokers(PHP_INT_MAX);
        
        $position = $allBrokers->search(function ($broker) use ($brokerId) {
            return $broker->id == $brokerId;
        });

        return $position !== false ? $position + 1 : null;
    }

    /**
     * Get broker performance percentile
     * 
     * @param int $brokerId
     * @return float|null Percentile (0-100)
     */
    public function getBrokerPercentile($brokerId)
    {
        $rank = $this->getBrokerRank($brokerId);
        
        if (!$rank) {
            return null;
        }

        $totalBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereNull('suspended_at')
            ->count();

        if ($totalBrokers == 0) {
            return null;
        }

        return round((($totalBrokers - $rank + 1) / $totalBrokers) * 100, 1);
    }
}
