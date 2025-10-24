<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityService;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminActivityController extends Controller
{
    protected $activityService;

    public function __construct(AdminActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display the admin activity audit dashboard
     */
    public function index(Request $request)
    {
        $filters = $request->only(['admin_id', 'action', 'target_type', 'date_from', 'date_to', 'search']);
        $activities = $this->activityService->searchActivities($filters, 20);
        
        $stats = $this->activityService->getActivityStats('today');
        $weeklyStats = $this->activityService->getActivityStats('week');
        $monthlyStats = $this->activityService->getActivityStats('month');
        
        $availableActions = $this->activityService->getAvailableActions();
        $availableTargetTypes = $this->activityService->getAvailableTargetTypes();
        
        // Get admin users for filter dropdown
        $adminUsers = User::where('role', 'admin')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Activity/Dashboard', [
            'activities' => $activities,
            'stats' => [
                'today' => $stats,
                'week' => $weeklyStats,
                'month' => $monthlyStats,
            ],
            'filters' => $filters,
            'availableActions' => $availableActions,
            'availableTargetTypes' => $availableTargetTypes,
            'adminUsers' => $adminUsers,
        ]);
    }

    /**
     * Get activity statistics for API
     */
    public function stats(Request $request)
    {
        $period = $request->get('period', 'today');
        $stats = $this->activityService->getActivityStats($period);
        
        return response()->json($stats);
    }

    /**
     * Get recent activities for a specific admin
     */
    public function adminActivities(Request $request, $adminId)
    {
        $limit = $request->get('limit', 10);
        $activities = $this->activityService->getRecentActivities($adminId, $limit);
        
        return response()->json($activities);
    }

    /**
     * Get activities for a specific target
     */
    public function targetActivities(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string',
            'target_id' => 'required|integer',
        ]);
        
        $targetType = $request->get('target_type');
        $targetId = $request->get('target_id');
        $limit = $request->get('limit', 10);
        
        // Create a mock target object for the service method
        $target = new class {
            public $id;
            public function __construct($id) {
                $this->id = $id;
            }
        };
        $target->id = $targetId;
        
        // Override the class name for the query
        $activities = AdminActivityLog::with(['admin'])
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->latest()
            ->limit($limit)
            ->get();
        
        return response()->json($activities);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $filters = $request->only(['admin_id', 'action', 'target_type', 'date_from', 'date_to', 'search']);
        $activities = $this->activityService->searchActivities($filters, 1000);
        
        $csvData = [];
        $csvData[] = ['Date', 'Admin', 'Action', 'Target Type', 'Target ID', 'Details', 'IP Address'];
        
        foreach ($activities->items() as $activity) {
            $csvData[] = [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->admin->name ?? 'Unknown',
                $activity->action,
                $activity->target_type ?? 'N/A',
                $activity->target_id ?? 'N/A',
                $activity->formatted_details ?? 'N/A',
                $activity->ip_address ?? 'N/A',
            ];
        }
        
        $filename = 'admin_activity_log_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activity summary for dashboard widgets
     */
    public function summary(Request $request)
    {
        $period = $request->get('period', 'today');
        
        $stats = $this->activityService->getActivityStats($period);
        
        // Get recent critical activities
        $criticalActions = ['user_suspended', 'user_rejected', 'property_deleted', 'bulk_user_action'];
        $recentCritical = AdminActivityLog::with(['admin', 'target'])
            ->whereIn('action', $criticalActions)
            ->recent(7)
            ->latest()
            ->limit(5)
            ->get();
        
        // Get most active admins
        $topAdmins = AdminActivityLog::with('admin')
            ->selectRaw('admin_id, COUNT(*) as activity_count')
            ->recent(30)
            ->groupBy('admin_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'admin' => $item->admin,
                    'activity_count' => $item->activity_count,
                ];
            });
        
        return response()->json([
            'stats' => $stats,
            'recent_critical' => $recentCritical,
            'top_admins' => $topAdmins,
        ]);
    }

    /**
     * Clean old activity logs (maintenance endpoint)
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days_to_keep' => 'required|integer|min:30|max:1095', // 30 days to 3 years
        ]);
        
        $daysToKeep = $request->get('days_to_keep', 365);
        $deletedCount = $this->activityService->cleanOldLogs($daysToKeep);
        
        // Log this cleanup activity
        $this->activityService->logSystemActivity('activity_logs_cleaned', [
            'days_to_keep' => $daysToKeep,
            'deleted_count' => $deletedCount,
        ]);
        
        return response()->json([
            'message' => "Successfully deleted {$deletedCount} old activity logs.",
            'deleted_count' => $deletedCount,
        ]);
    }
}