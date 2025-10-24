<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflowable_type',
        'workflowable_id',
        'broker_id',
        'client_id',
        'workflow_type',
        'status',
        'scheduled_at',
        'completed_at',
        'workflow_data',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'workflow_data' => 'array',
    ];

    /**
     * Get the workflowable entity (inquiry or transaction)
     */
    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the broker associated with the workflow
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    /**
     * Get the client associated with the workflow
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope for workflows by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for workflows by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('workflow_type', $type);
    }

    /**
     * Scope for pending workflows
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue workflows
     */
    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<', now())
                    ->where('status', 'pending');
    }

    /**
     * Scope for workflows for a specific broker
     */
    public function scopeForBroker($query, int $brokerId)
    {
        return $query->where('broker_id', $brokerId);
    }

    /**
     * Scope for workflows for a specific client
     */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for workflows within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if workflow is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->scheduled_at < now() && $this->status === 'pending';
    }

    /**
     * Get workflow duration in minutes
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->completed_at);
    }

    /**
     * Get workflow duration in human readable format
     */
    public function getDurationAttribute(): string
    {
        if (!$this->completed_at) {
            return 'In Progress';
        }

        return $this->created_at->diffForHumans($this->completed_at, true);
    }

    /**
     * Get workflow type label
     */
    public function getWorkflowTypeLabelAttribute(): string
    {
        return match($this->workflow_type) {
            'inquiry_response' => 'Inquiry Response',
            'escalation' => 'Escalation',
            'follow_up' => 'Follow Up',
            'transaction_update' => 'Transaction Update',
            default => ucfirst(str_replace('_', ' ', $this->workflow_type))
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'escalated' => 'Escalated',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'completed' => 'green',
            'escalated' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get workflow priority
     */
    public function getPriorityAttribute(): string
    {
        $data = $this->workflow_data ?? [];
        
        return match($data['priority'] ?? 'normal') {
            'high' => 'High',
            'normal' => 'Normal',
            'low' => 'Low',
            default => 'Normal'
        };
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'High' => 'red',
            'Normal' => 'blue',
            'Low' => 'green',
            default => 'blue'
        };
    }

    /**
     * Complete the workflow
     */
    public function complete(string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Cancel the workflow
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'notes' => $reason ?? $this->notes,
        ]);
    }

    /**
     * Escalate the workflow
     */
    public function escalate(string $reason = null): void
    {
        $this->update([
            'status' => 'escalated',
            'completed_at' => now(),
            'notes' => $reason ?? $this->notes,
        ]);
    }

    /**
     * Start the workflow
     */
    public function start(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Get workflow summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'workflow_type' => $this->workflow_type_label,
            'status' => $this->status_label,
            'status_color' => $this->status_color,
            'priority' => $this->priority,
            'priority_color' => $this->priority_color,
            'broker_name' => $this->broker->name ?? 'Unknown',
            'client_name' => $this->client->name ?? 'Unknown',
            'scheduled_at' => $this->scheduled_at?->format('M j, Y H:i'),
            'completed_at' => $this->completed_at?->format('M j, Y H:i'),
            'duration' => $this->duration,
            'is_overdue' => $this->is_overdue,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('M j, Y H:i'),
        ];
    }

    /**
     * Get workflow statistics
     */
    public static function getWorkflowStatistics(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $workflows = self::where('created_at', '>=', $startDate)->get();
        
        return [
            'total_workflows' => $workflows->count(),
            'by_status' => $workflows->groupBy('status')->map->count(),
            'by_type' => $workflows->groupBy('workflow_type')->map->count(),
            'overdue_count' => $workflows->where('is_overdue', true)->count(),
            'completion_rate' => $this->calculateCompletionRate($workflows),
            'average_duration_minutes' => $this->calculateAverageDuration($workflows),
            'escalation_rate' => $this->calculateEscalationRate($workflows),
        ];
    }

    /**
     * Get broker workflow statistics
     */
    public static function getBrokerWorkflowStatistics(int $brokerId, int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $workflows = self::where('broker_id', $brokerId)
            ->where('created_at', '>=', $startDate)
            ->get();
        
        return [
            'total_workflows' => $workflows->count(),
            'by_status' => $workflows->groupBy('status')->map->count(),
            'by_type' => $workflows->groupBy('workflow_type')->map->count(),
            'overdue_count' => $workflows->where('is_overdue', true)->count(),
            'completion_rate' => $this->calculateCompletionRate($workflows),
            'average_duration_minutes' => $this->calculateAverageDuration($workflows),
            'response_rate' => $this->calculateResponseRate($workflows),
        ];
    }

    /**
     * Calculate completion rate
     */
    protected static function calculateCompletionRate($workflows): float
    {
        $total = $workflows->count();
        $completed = $workflows->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average duration
     */
    protected static function calculateAverageDuration($workflows): float
    {
        $completedWorkflows = $workflows->where('status', 'completed')
            ->whereNotNull('completed_at');

        if ($completedWorkflows->isEmpty()) {
            return 0;
        }

        $totalMinutes = $completedWorkflows->sum('duration_in_minutes');
        return round($totalMinutes / $completedWorkflows->count(), 2);
    }

    /**
     * Calculate escalation rate
     */
    protected static function calculateEscalationRate($workflows): float
    {
        $total = $workflows->count();
        $escalated = $workflows->where('status', 'escalated')->count();

        return $total > 0 ? round(($escalated / $total) * 100, 2) : 0;
    }

    /**
     * Calculate response rate
     */
    protected static function calculateResponseRate($workflows): float
    {
        $inquiryWorkflows = $workflows->where('workflow_type', 'inquiry_response');
        $total = $inquiryWorkflows->count();
        $completed = $inquiryWorkflows->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}

