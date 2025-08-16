<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Client;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Enhanced statistics with trends
        $stats = [
            'totalProperties' => $user->properties()->count(),
            'activeProperties' => $user->properties()->where('status', 'available')->count(),
            'totalClients' => $user->clients()->count(),
            'activeInquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                $query->where('broker_id', $user->id);
            })->where('status', 'pending')->count(),
            'completedTransactions' => $user->transactions()->where('status', 'completed')->count(),
            'totalCommission' => $user->transactions()->where('status', 'completed')->sum('commission_amount'),
            'monthlyStats' => $this->getMonthlyStats($user),
            'recentActivity' => $this->getRecentActivity($user),
        ];

        $recentInquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })
        ->with(['property', 'client'])
        ->latest()
        ->take(10)
        ->get()
        ->map(function($inquiry) {
            return [
                'id' => $inquiry->id,
                'property' => $inquiry->property->title,
                'client' => $inquiry->client->name ?? 'Anonymous',
                'amount' => '₱' . number_format($inquiry->property->price),
                'date' => $inquiry->created_at->format('M d, Y'),
                'status' => $inquiry->status,
            ];
        });

        return Inertia::render('Broker/ModernDashboard', [
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
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
            
            $monthlyData->push([
                'month' => $date->format('M Y'),
                'inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'transactions' => $user->transactions()
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'commission' => $user->transactions()
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('commission_amount'),
                'properties_added' => $user->properties()
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
            ]);
        }

        // Property performance
        $propertyStats = $user->properties()
            ->withCount(['inquiries', 'transactions'])
            ->orderBy('inquiries_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price' => $property->price,
                    'inquiries_count' => $property->inquiries_count,
                    'transactions_count' => $property->transactions_count,
                    'conversion_rate' => $property->inquiries_count > 0 
                        ? round(($property->transactions_count / $property->inquiries_count) * 100, 1)
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
                'averageCommission' => $user->transactions()
                    ->where('status', 'completed')
                    ->avg('commission_amount'),
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
                'total_commission' => $user->transactions()->where('status', 'completed')->sum('commission_amount'),
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
            'currentMonth' => [
                'inquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                    $query->where('broker_id', $user->id);
                })->where('created_at', '>=', $currentMonth)->count(),
                'transactions' => $user->transactions()->where('created_at', '>=', $currentMonth)->count(),
            ],
            'lastMonth' => [
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

        // Recent inquiries
        $inquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })->with('property')->latest()->take(5)->get();

        foreach ($inquiries as $inquiry) {
            $activities->push([
                'type' => 'inquiry',
                'message' => "New inquiry for {$inquiry->property->title}",
                'date' => $inquiry->created_at,
            ]);
        }

        // Recent transactions
        $transactions = $user->transactions()->with('property')->latest()->take(3)->get();
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

        $completedTransactions = $user->transactions()->where('status', 'completed')->count();

        return $totalInquiries > 0 ? round(($completedTransactions / $totalInquiries) * 100, 1) : 0;
    }

    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return Inertia::render('Broker/PendingApproval');
    }
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
