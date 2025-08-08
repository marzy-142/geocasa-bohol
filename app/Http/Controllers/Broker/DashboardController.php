<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Client;

class DashboardController extends Controller
{
    /**
     * Display broker dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Real statistics for the broker
        $stats = [
            'totalProperties' => $user->properties()->count(),
            'activeProperties' => $user->properties()->where('status', 'available')->count(),
            'totalClients' => $user->clients()->count(),
            'activeInquiries' => Inquiry::whereHas('property', function($query) use ($user) {
                $query->where('broker_id', $user->id);
            })->where('status', 'pending')->count(),
            'completedTransactions' => $user->transactions()->where('status', 'completed')->count(),
            'totalCommission' => $user->transactions()->where('status', 'completed')->sum('commission_amount'),
        ];

        // Recent activities
        $recentProperties = $user->properties()->latest()->take(5)->get();
        $recentInquiries = Inquiry::whereHas('property', function($query) use ($user) {
            $query->where('broker_id', $user->id);
        })->with(['property', 'client'])->latest()->take(5)->get();
        
        $recentTransactions = $user->transactions()
            ->with(['property', 'client'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Broker/ModernDashboard', [
            'stats' => $stats,
            'recentProperties' => $recentProperties,
            'recentInquiries' => $recentInquiries,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return Inertia::render('Broker/PendingApproval');
    }
}
