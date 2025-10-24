<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerClientRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'broker_id',
        'relationship_type',
        'assignment_method',
        'assignment_reason',
        'assigned_by',
        'assigned_at',
        'ended_at',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the client that owns the relationship
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the broker that owns the relationship
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    /**
     * Get the user who assigned the relationship
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active relationships
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope for relationships by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    /**
     * Scope for relationships by assignment method
     */
    public function scopeByAssignmentMethod($query, string $method)
    {
        return $query->where('assignment_method', $method);
    }

    /**
     * Scope for relationships within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('assigned_at', [$startDate, $endDate]);
    }

    /**
     * Check if relationship is active
     */
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->ended_at);
    }

    /**
     * Get relationship duration in days
     */
    public function getDurationInDaysAttribute(): ?int
    {
        if (!$this->assigned_at) {
            return null;
        }

        $endDate = $this->ended_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }

    /**
     * Get relationship duration in human readable format
     */
    public function getDurationAttribute(): string
    {
        if (!$this->assigned_at) {
            return 'Unknown';
        }

        $endDate = $this->ended_at ?? now();
        return $this->assigned_at->diffForHumans($endDate, true);
    }

    /**
     * End the relationship
     */
    public function end(string $reason = null): void
    {
        $this->update([
            'ended_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'end_reason' => $reason,
                'ended_by' => auth()->id(),
                'ended_at' => now()->toISOString(),
            ])
        ]);
    }

    /**
     * Get relationship type label
     */
    public function getRelationshipTypeLabelAttribute(): string
    {
        return match($this->relationship_type) {
            'primary' => 'Primary Relationship',
            'secondary' => 'Secondary Relationship',
            'inquiry_specific' => 'Inquiry Specific',
            default => ucfirst($this->relationship_type)
        };
    }

    /**
     * Get assignment method label
     */
    public function getAssignmentMethodLabelAttribute(): string
    {
        return match($this->assignment_method) {
            'auto' => 'Automatic Assignment',
            'manual' => 'Manual Assignment',
            'escalated' => 'Escalation Assignment',
            'reassigned' => 'Reassignment',
            default => ucfirst($this->assignment_method)
        };
    }

    /**
     * Get relationship summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client->name ?? 'Unknown',
            'broker_name' => $this->broker->name ?? 'Unknown',
            'relationship_type' => $this->relationship_type_label,
            'assignment_method' => $this->assignment_method_label,
            'assignment_reason' => $this->assignment_reason,
            'assigned_at' => $this->assigned_at?->format('M j, Y H:i'),
            'ended_at' => $this->ended_at?->format('M j, Y H:i'),
            'duration' => $this->duration,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Get statistics for a broker
     */
    public static function getBrokerStatistics(int $brokerId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $relationships = self::where('broker_id', $brokerId)
            ->where('assigned_at', '>=', $startDate)
            ->get();

        $activeRelationships = $relationships->whereNull('ended_at');

        return [
            'total_relationships' => $relationships->count(),
            'active_relationships' => $activeRelationships->count(),
            'ended_relationships' => $relationships->whereNotNull('ended_at')->count(),
            'by_type' => $relationships->groupBy('relationship_type')->map->count(),
            'by_method' => $relationships->groupBy('assignment_method')->map->count(),
            'average_duration_days' => $relationships->whereNotNull('ended_at')->avg('duration_in_days'),
            'longest_relationship_days' => $relationships->max('duration_in_days'),
            'relationship_turnover_rate' => $this->calculateTurnoverRate($relationships),
        ];
    }

    /**
     * Get statistics for a client
     */
    public static function getClientStatistics(int $clientId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $relationships = self::where('client_id', $clientId)
            ->where('assigned_at', '>=', $startDate)
            ->get();

        return [
            'total_relationships' => $relationships->count(),
            'current_broker' => $relationships->whereNull('ended_at')->first()?->broker,
            'broker_changes' => $relationships->whereNotNull('ended_at')->count(),
            'average_relationship_duration_days' => $relationships->whereNotNull('ended_at')->avg('duration_in_days'),
            'relationship_history' => $relationships->map->summary,
        ];
    }

    /**
     * Calculate turnover rate
     */
    protected static function calculateTurnoverRate($relationships): float
    {
        $total = $relationships->count();
        $ended = $relationships->whereNotNull('ended_at')->count();

        return $total > 0 ? round(($ended / $total) * 100, 2) : 0;
    }
}

