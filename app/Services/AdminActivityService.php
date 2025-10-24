<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AdminActivityService
{
    /**
     * Log admin activity with comprehensive details
     */
    public function log(string $action, $target = null, array $details = [], ?Request $request = null): ?AdminActivityLog
    {
        try {
            $request = $request ?? request();
            
            return AdminActivityLog::create([
                'admin_id' => Auth::id(),
                'action' => $action,
                'target_type' => $target ? get_class($target) : null,
                'target_id' => $target?->id,
                'details' => $details,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database logging fails
            Log::error("Failed to log admin activity to database: {$e->getMessage()}");
            Log::info("Admin Activity: {$action}", [
                'admin_id' => Auth::id(),
                'target_type' => $target ? get_class($target) : null,
                'target_id' => $target?->id,
                'details' => $details,
            ]);
            return null;
        }
    }

    /**
     * Log user-related activities
     */
    public function logUserActivity(string $action, $user, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, $user, $details);
    }

    /**
     * Log property-related activities
     */
    public function logPropertyActivity(string $action, $property, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, $property, $details);
    }

    /**
     * Log transaction-related activities
     */
    public function logTransactionActivity(string $action, $transaction, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, $transaction, $details);
    }

    /**
     * Log seller request activities
     */
    public function logSellerRequestActivity(string $action, $sellerRequest, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, $sellerRequest, $details);
    }

    /**
     * Log compliance report activities
     */
    public function logComplianceActivity(string $action, $complianceReport, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, $complianceReport, $details);
    }

    /**
     * Log system-wide activities (no specific target)
     */
    public function logSystemActivity(string $action, array $details = []): ?AdminActivityLog
    {
        return $this->log($action, null, $details);
    }

    /**
     * Get recent activities for a specific admin
     */
    public function getRecentActivities(int $adminId, int $limit = 10)
    {
        return AdminActivityLog::with(['admin', 'target'])
            ->byAdmin($adminId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get activities for a specific target
     */
    public function getTargetActivities($target, int $limit = 10)
    {
        return AdminActivityLog::with(['admin'])
            ->where('target_type', get_class($target))
            ->where('target_id', $target->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(string $period = 'today')
    {
        $query = AdminActivityLog::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->thisWeek();
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'recent':
                $query->recent(7);
                break;
        }

        return [
            'total_activities' => $query->count(),
            'unique_admins' => $query->distinct('admin_id')->count('admin_id'),
            'activities_by_action' => $query->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'activities_by_admin' => $query->with('admin')
                ->selectRaw('admin_id, COUNT(*) as count')
                ->groupBy('admin_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->admin->name ?? 'Unknown' => $item->count];
                })
                ->toArray(),
        ];
    }

    /**
     * Search activities with filters
     */
    public function searchActivities(array $filters = [], int $perPage = 15)
    {
        $query = AdminActivityLog::with(['admin', 'target'])->latest();

        if (!empty($filters['admin_id'])) {
            $query->byAdmin($filters['admin_id']);
        }

        if (!empty($filters['action'])) {
            $query->byAction($filters['action']);
        }

        if (!empty($filters['target_type'])) {
            $query->byTargetType($filters['target_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('action', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('admin', function ($adminQuery) use ($filters) {
                      $adminQuery->where('name', 'like', '%' . $filters['search'] . '%')
                                 ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                  })
                  ->orWhere('details', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get available actions for filtering
     */
    public function getAvailableActions(): array
    {
        return AdminActivityLog::ACTIONS;
    }

    /**
     * Get available target types for filtering
     */
    public function getAvailableTargetTypes(): array
    {
        return AdminActivityLog::TARGET_TYPES;
    }

    /**
     * Clean old activity logs (for maintenance)
     */
    public function cleanOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        return AdminActivityLog::where('created_at', '<', $cutoffDate)->delete();
    }
}