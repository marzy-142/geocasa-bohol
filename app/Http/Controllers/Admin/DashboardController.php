<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Inquiry;
use App\Models\SellerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
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
            'totalTransactions' => Transaction::where('status', 'completed')->count(),
        ];

        // Get top performing broker
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
            ], 'final_price')
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

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'topBroker' => $topBroker,
            'pendingBrokers' => $pendingBrokers,
            'systemHealth' => $systemHealth,
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
}