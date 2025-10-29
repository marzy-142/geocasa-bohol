<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Inquiry;
use App\Models\SellerRequest;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Display admin dashboard with real system statistics
     */
    public function index()
    {
        // Real system statistics
        $stats = [
            'totalBrokers' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->count(),
            'pendingApprovals' => User::pendingApplications()->count(),
            'totalProperties' => Property::count(),
            'totalTransactions' => Transaction::where('status', 'finalized')->count(),
            'activeBrokers' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->whereHas('properties')
                ->count(),
            'totalInquiries' => Inquiry::count(),
            'conversionRate' => $this->calculateConversionRate(),
            'monthlyGrowth' => $this->calculateMonthlyGrowth(),
        ];

        // Get top performing broker (enhanced logic)
        $topBroker = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount([
                'transactions as total_sales' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ])
            ->withSum([
                'transactions as total_sales_value' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ], DB::raw('COALESCE(final_price, offered_price)'))
            ->having('total_sales', '>', 0) // Only brokers with actual sales
            ->orderByDesc('total_sales')
            ->first();

        // Get pending broker applications
        $pendingBrokers = User::pendingApplications()
            ->select(['id', 'name', 'email', 'created_at'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($broker) {
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'applied' => $broker->created_at->format('Y-m-d'),
                ];
            });

        // System health checks (basic implementation)
        $systemHealth = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
        ];

        // Get system-wide reminders for admin
        $reminders = $this->reminderService->getBrokerReminders(null);

        // Seller request assignment statistics
        $unassignedSellerRequests = SellerRequest::whereNull('assigned_broker_id')
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
        
        $assignedSellerRequests = SellerRequest::whereNotNull('assigned_broker_id')
            ->whereIn('status', ['under_review', 'approved', 'listed'])
            ->count();
        
        $activeBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereHas('assignedSellerRequests', function($query) {
                $query->whereIn('status', ['under_review', 'approved', 'listed']);
            })
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'topBroker' => $topBroker,
            'pendingBrokers' => $pendingBrokers,
            'systemHealth' => $systemHealth,
            'reminders' => $reminders,
            'unassignedSellerRequests' => $unassignedSellerRequests,
            'assignedSellerRequests' => $assignedSellerRequests,
            'activeBrokers' => $activeBrokers,
        ]);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection failed'];
        }
    }

    /**
     * Check storage accessibility
     */
    private function checkStorageHealth(): array
    {
        try {
            $testFile = 'health-check-' . time() . '.txt';
            Storage::put($testFile, 'test');
            Storage::delete($testFile);
            return ['status' => 'healthy', 'message' => 'Available'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage error'];
        }
    }

    /**
     * Check cache functionality
     */
    private function checkCacheHealth(): array
    {
        try {
            $key = 'health-check-' . time();
            Cache::put($key, 'test', 60);
            $value = Cache::get($key);
            Cache::forget($key);
            
            return $value === 'test' 
                ? ['status' => 'healthy', 'message' => 'Working']
                : ['status' => 'warning', 'message' => 'Cache miss'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache error'];
        }
    }

    /**
     * Calculate conversion rate (inquiries to transactions)
     */
    private function calculateConversionRate(): float
    {
        $totalInquiries = Inquiry::count();
        $totalTransactions = Transaction::where('status', 'finalized')->count();
        
        if ($totalInquiries === 0) {
            return 0;
        }
        
        return round(($totalTransactions / $totalInquiries) * 100, 1);
    }

    /**
     * Calculate monthly growth percentage
     */
    private function calculateMonthlyGrowth(): array
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        
        // Get current month data
        $currentBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->count();
            
        $currentProperties = Property::whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->count();
            
        $currentTransactions = Transaction::where('status', 'finalized')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->count();
        
        // Get last month data
        $lastMonthBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$lastMonth])
            ->count();
            
        $lastMonthProperties = Property::whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$lastMonth])
            ->count();
            
        $lastMonthTransactions = Transaction::where('status', 'finalized')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$lastMonth])
            ->count();
        
        return [
            'brokers' => $this->calculateGrowthPercentage($currentBrokers, $lastMonthBrokers),
            'properties' => $this->calculateGrowthPercentage($currentProperties, $lastMonthProperties),
            'transactions' => $this->calculateGrowthPercentage($currentTransactions, $lastMonthTransactions),
        ];
    }

    /**
     * Calculate growth percentage between two values
     */
    private function calculateGrowthPercentage(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}