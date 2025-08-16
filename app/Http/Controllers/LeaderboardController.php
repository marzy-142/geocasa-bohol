<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    /**
     * Display the simplified leaderboard - just the top broker
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all-time');
        
        // Get the broker with the most sales
        $topBroker = $this->getTopBroker($period);
        
        return Inertia::render('Leaderboard/Index', [
            'topBroker' => $topBroker,
            'period' => $period,
        ]);
    }

    /**
     * Get the broker with the most sales
     */
    private function getTopBroker($period = 'all-time')
    {
        $query = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'transactions as total_sales' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                }
            ])
            ->withSum([
                'transactions as total_sales_value' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                }
            ], 'final_price')
            ->withSum([
                'transactions as total_commission' => function ($query) use ($period) {
                    $query->where('status', 'finalized');
                    $this->applyPeriodFilter($query, $period);
                }
            ], 'commission_amount');

        // Get the broker with the highest sales count
        $topBroker = $query->orderByDesc('total_sales')->first();

        if ($topBroker) {
            $topBroker->total_commission = $topBroker->total_commission ?? 0;
            $topBroker->total_sales_value = $topBroker->total_sales_value ?? 0;
        }

        return $topBroker;
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, $period)
    {
        switch ($period) {
            case 'this-year':
                $query->whereYear('finalized_date', now()->year);
                break;
            case 'this-month':
                $query->whereYear('finalized_date', now()->year)
                      ->whereMonth('finalized_date', now()->month);
                break;
            case 'last-30-days':
                $query->where('finalized_date', '>=', now()->subDays(30));
                break;
            case 'last-90-days':
                $query->where('finalized_date', '>=', now()->subDays(90));
                break;
            case 'all-time':
            default:
                // No filter for all-time
                break;
        }
    }
}