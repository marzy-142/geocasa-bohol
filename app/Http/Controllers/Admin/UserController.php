<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\AdminActivityLog;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of all users with filtering and search
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('suspended_at');
            } elseif ($request->status === 'suspended') {
                $query->whereNotNull('suspended_at');
            }
        }
        
        if ($request->filled('approval_status')) {
            $query->where('application_status', $request->approval_status);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $users = $query->withCount(['properties', 'clients', 'transactions'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);
        
        // Add computed status field to each user
        $users->getCollection()->transform(function ($user) {
            $user->status = $user->suspended_at ? 'suspended' : 'active';
            return $user;
        });
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'status', 'approval_status', 'search']),
            'stats' => [
                'total' => User::count(),
                'brokers' => User::where('role', 'broker')->count(),
                'clients' => User::where('role', 'client')->count(),
                'suspended' => User::whereNotNull('suspended_at')->count(),
            ]
        ]);
    }
    
    /**
     * Display detailed user profile
     */
    public function show(User $user)
    {
        $user->load([
            'properties' => function ($query) {
                $query->latest()->limit(10);
            },
            'clients' => function ($query) {
                $query->latest()->limit(10);
            },
            'transactions' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);
        
        // Add computed status field
        $user->status = $user->suspended_at ? 'suspended' : 'active';
        
        // Get activity logs (placeholder for now)
        $activityLogs = collect([
            // Add some sample activity logs or fetch from database
            [
                'id' => 1,
                'action' => 'Account Created',
                'description' => 'User account was created',
                'created_at' => $user->created_at,
                'admin_name' => 'System'
            ],
            [
                'id' => 2,
                'action' => 'Profile Updated',
                'description' => 'User profile information was updated',
                'created_at' => $user->updated_at,
                'admin_name' => 'System'
            ]
        ]);
        
        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'activityLogs' => $activityLogs,
            'stats' => [
                'totalProperties' => $user->properties()->count(),
                'totalClients' => $user->clients()->count(),
                'totalTransactions' => $user->transactions()->count(),
                'totalCommission' => $user->transactions()->where('status', 'completed')->sum('commission_amount'),
            ]
        ]);
    }
    
    /**
     * Suspend a user account
     */
    public function suspend(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365', // days
        ]);
        
        $suspendedUntil = $request->duration 
            ? now()->addDays($request->duration) 
            : null;
        
        $user->update([
            'suspended_at' => now(),
            'suspended_until' => $suspendedUntil,
            'suspension_reason' => $request->reason,
            'suspended_by' => auth()->user()->id,
        ]);
        
        // Log admin activity
        $this->logAdminActivity('user_suspended', $user, [
            'reason' => $request->reason,
            'duration' => $request->duration,
        ]);
        
        return back()->with('success', 'User suspended successfully.');
    }
    
    /**
     * Reactivate a suspended user
     */
    public function reactivate(User $user)
    {
        $user->update([
            'suspended_at' => null,
            'suspended_until' => null,
            'suspension_reason' => null,
            'suspended_by' => null,
        ]);
        
        // Log admin activity
        $this->logAdminActivity('user_reactivated', $user);
        
        return back()->with('success', 'User reactivated successfully.');
    }
    
    /**
     * Handle bulk actions on users
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:suspend,reactivate,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required_if:action,suspend|string|max:500',
        ]);
        
        $users = User::whereIn('id', $request->user_ids)->get();
        
        foreach ($users as $user) {
            switch ($request->action) {
                case 'suspend':
                    $user->update([
                        'suspended_at' => now(),
                        'suspension_reason' => $request->reason,
                        'suspended_by' => auth()->user()->id,
                    ]);
                    break;
                    
                case 'reactivate':
                    $user->update([
                        'suspended_at' => null,
                        'suspended_until' => null,
                        'suspension_reason' => null,
                        'suspended_by' => null,
                    ]);
                    break;
                    
                case 'delete':
                    // Soft delete for data integrity
                    $user->delete();
                    break;
            }
        }
        
        // Log bulk action
        $this->logAdminActivity('bulk_user_action', null, [
            'action' => $request->action,
            'user_count' => count($request->user_ids),
            'reason' => $request->reason ?? null,
        ]);
        
        return back()->with('success', "Bulk action '{$request->action}' completed for " . count($request->user_ids) . " users.");
    }
    
    /**
     * Log admin activity for audit trail
     */
    private function logAdminActivity($action, $target = null, $details = [])
    {
        try {
            AdminActivityLog::create([
                'admin_id' => auth()->user()->id,
                'action' => $action,
                'target_type' => $target ? get_class($target) : null,
                'target_id' => $target?->id,
                'details' => $details,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database logging fails
            Log::error("Failed to log admin activity to database: {$e->getMessage()}");
            Log::info("Admin Activity: {$action}", [
                'admin_id' => auth()->user()->id,
                'target_type' => $target ? get_class($target) : null,
                'target_id' => $target?->id,
                'details' => $details,
            ]);
        }
    }
}