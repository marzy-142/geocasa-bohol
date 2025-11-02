<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Client;
use App\Services\DatabaseOptimizationService;
use App\Services\ReminderService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $optimizationService;
    protected $reminderService;

    public function __construct(DatabaseOptimizationService $optimizationService, ReminderService $reminderService)
    {
        $this->optimizationService = $optimizationService;
        $this->reminderService = $reminderService;
    }

    public function index()
    {
        $user = auth()->user();
        $brokerId = $user->id;
        
        // Get cached dashboard statistics
        $cachedStats = $this->optimizationService->getBrokerDashboardStats($brokerId);

        // Enhanced statistics with trends
        $stats = [
            'totalProperties' => $cachedStats['totalProperties'],
            'activeProperties' => $cachedStats['activeProperties'],
            'totalClients' => $cachedStats['totalClients'],
            'activeInquiries' => $cachedStats['activeInquiries'],
            'completedTransactions' => $cachedStats['completedTransactions'],
            'monthlyStats' => $this->getMonthlyStats($user),
            'recentActivity' => $this->getRecentActivity($user),
        ];

        // Get recent inquiries for the broker with eager loading to prevent N+1 queries
        $recentInquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })
        ->with(['property:id,title,total_price', 'client:id,name'])
        ->select('inquiries.id', 'inquiries.property_id', 'inquiries.client_id', 'inquiries.status', 'inquiries.created_at')
        ->orderBy('inquiries.created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function($inquiry) {
            return [
                'id' => $inquiry->id,
                'property' => $inquiry->property->title,
                'client' => $inquiry->client->name ?? 'Anonymous',
                // Ensure numeric formatting works even if total_price is decimal/string
                'amount' => '₱' . number_format((float) $inquiry->property->total_price),
                'date' => $inquiry->created_at->format('M d, Y'),
                'status' => $inquiry->status,
            ];
        });

        // Get reminders for the broker
        $reminders = $this->reminderService->getBrokerReminders($user->id);

        // Get recent transactions for the broker
        $recentTransactions = Transaction::where('broker_id', $user->id)
            ->with(['property:id,title,total_price', 'client:id,name'])
            ->select('id', 'property_id', 'client_id', 'status', 'final_price', 'offered_price', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'property' => $transaction->property,
                    'client' => $transaction->client,
                    'final_price' => $transaction->final_price,
                    'offered_price' => $transaction->offered_price,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                ];
            });

        return Inertia::render('Broker/Dashboard', [
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
            'recentTransactions' => $recentTransactions,
            'reminders' => $reminders,
        ]);
    }

    /**
     * Show analytics page with detailed performance metrics
     */
    public function analytics()
    {
        $user = auth()->user();
        
        // Get performance data for the last 12 months
        $monthlyData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Get finalized transactions for this month with commission calculation
            $monthTransactions = $user->transactions()
                ->where('status', 'finalized')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();
            
            $monthCommission = $monthTransactions->sum(function($transaction) {
                return $transaction->final_price ?? $transaction->offered_price ?? 0;
            });
            
            $monthlyData->push([
                'month' => $date->format('M Y'),
                'inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'transactions' => $monthTransactions->count(),
                'commission' => $monthCommission,
                'properties_added' => $user->properties()
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
            ]);
        }

        // Property performance
        $propertyStats = $user->properties()
            ->withCount('inquiries')
            ->orderBy('inquiries_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($property) {
                $finalizedTransactionsCount = $property->transactions()->where('status', 'finalized')->count();
                
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price' => $property->total_price,
                    'inquiries_count' => $property->inquiries_count,
                    'transactions_count' => $finalizedTransactionsCount,
                    'conversion_rate' => $property->inquiries_count > 0 
                        ? round(($finalizedTransactionsCount / $property->inquiries_count) * 100, 1)
                        : 0,
                ];
            });

        return Inertia::render('Broker/Analytics', [
            'monthlyData' => $monthlyData,
            'propertyStats' => $propertyStats,
            'totalStats' => [
                'totalInquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->count(),
                'conversionRate' => $this->calculateConversionRate($user),
                'averageCommission' => $this->calculateAverageCommission($user),
                'topPerformingProperty' => $propertyStats->first(),
            ],
        ]);
    }

    /**
     * Show reports page with downloadable reports
     */
    public function reports()
    {
        $user = auth()->user();
        
        // Generate report data
        $reportData = [
            'summary' => [
                'total_properties' => $user->properties()->count(),
                'total_inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->count(),
                'total_transactions' => $user->transactions()->count(),
            ],
            'recent_transactions' => $user->transactions()
                ->with(['property', 'client'])
                ->latest()
                ->take(20)
                ->get(),
            'property_performance' => $user->properties()
                ->withCount('inquiries')
                ->orderBy('inquiries_count', 'desc')
                ->get(),
        ];

        return Inertia::render('Broker/Reports', [
            'reportData' => $reportData,
        ]);
    }

    private function getMonthlyStats($user)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'current' => [
                'properties' => $user->properties()->where('created_at', '>=', $currentMonth)->count(),
                'inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->where('created_at', '>=', $currentMonth)->count(),
                'transactions' => $user->transactions()->where('created_at', '>=', $currentMonth)->count(),
            ],
            'previous' => [
                'properties' => $user->properties()->whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
                'inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
                'transactions' => $user->transactions()->whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            ],
        ];
    }

    private function getRecentActivity($user)
    {
        // Combine recent inquiries, transactions, and property updates
        $activities = collect();

        // Optimized recent inquiries
        $inquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })->with('property:id,title,broker_id')
        ->select('id', 'property_id', 'created_at')
        ->latest()->take(5)->get();

        foreach ($inquiries as $inquiry) {
            $activities->push([
                'type' => 'inquiry',
                'message' => "New inquiry for {$inquiry->property->title}",
                'date' => $inquiry->created_at,
            ]);
        }

        // Optimized recent transactions
        $transactions = $user->transactions()
            ->with('property:id,title,broker_id')
            ->select('id', 'property_id', 'status', 'created_at')
            ->latest()->take(3)->get();
        foreach ($transactions as $transaction) {
            $activities->push([
                'type' => 'transaction',
                'message' => "Transaction {$transaction->status} for {$transaction->property->title}",
                'date' => $transaction->created_at,
            ]);
        }

        return $activities->sortByDesc('date')->take(10)->values();
    }

    private function calculateConversionRate($user)
    {
        $totalInquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })->count();

        $finalizedTransactions = $user->transactions()->where('status', 'finalized')->count();

        return $totalInquiries > 0 ? round(($finalizedTransactions / $totalInquiries) * 100, 1) : 0;
    }

    private function calculateAverageCommission($user)
    {
        $finalizedTransactions = $user->transactions()
            ->where('status', 'finalized')
            ->get();

        if ($finalizedTransactions->isEmpty()) {
            return 0;
        }

        $totalCommission = $finalizedTransactions->sum(function($transaction) {
            return $transaction->final_price ?? $transaction->offered_price ?? 0;
        });

        return round($totalCommission / $finalizedTransactions->count(), 2);
    }

    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return Inertia::render('Broker/PendingApproval');
    }

    /**
     * Show rejected broker page
     */
    public function rejected()
    {
        $user = auth()->user();
        
        if ($user->role !== 'broker' || $user->application_status !== 'rejected') {
            return redirect()->route('dashboard');
        }
        
        return Inertia::render('Broker/Rejected', [
            'rejection_reason' => $user->rejection_reason,
            'reviewed_at' => $user->reviewed_at,
        ]);
    }
    
}
