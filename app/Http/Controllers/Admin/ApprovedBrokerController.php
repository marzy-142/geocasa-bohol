<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovedBrokerController extends Controller
{
    /**
     * Display a listing of approved brokers
     */
    public function index(Request $request)
    {
        $query = User::approvedBrokers();
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Performance filters
        if ($request->filled('performance')) {
            // Implementation for filtering by performance metrics
            // This would require additional logic based on transactions
        }
        
        $approvedBrokers = $query->withCount(['properties', 'clients', 'transactions'])
                              ->withSum('transactions as total_commission', 'commission_amount')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);
        
        return Inertia::render('Admin/ApprovedBrokers/Index', [
            'approvedBrokers' => $approvedBrokers,
            'filters' => $request->only(['search', 'performance']),
        ]);
    }
    
    /**
     * Display detailed broker profile
     */
    public function show(User $broker)
    {
        // Ensure the user is an approved broker
        if (!$broker->isApproved() || $broker->role !== 'broker') {
            abort(404);
        }
        
        $broker->load([
            'properties' => function ($query) {
                $query->latest()->limit(5);
            },
            'clients' => function ($query) {
                $query->latest()->limit(5);
            },
            'transactions' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);
        
        // Get performance metrics
        $performanceMetrics = [
            'total_sales' => $broker->transactions()->where('status', 'completed')->count(),
            'total_commission' => $broker->transactions()->where('status', 'completed')->sum('commission_amount'),
            'avg_days_to_close' => $broker->transactions()
                ->whereNotNull('inquiry_date')
                ->whereNotNull('finalized_date')
                ->selectRaw('AVG(DATEDIFF(finalized_date, inquiry_date)) as avg_days')
                ->value('avg_days') ?? 0,
            'client_satisfaction' => 4.5, // Placeholder - would need implementation
        ];
        
        return Inertia::render('Admin/ApprovedBrokers/Show', [
            'broker' => $broker,
            'performanceMetrics' => $performanceMetrics,
        ]);
    }
}