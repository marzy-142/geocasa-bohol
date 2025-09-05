<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'compliance_report_id',
        'investigator_id',
        'action_type',
        'description',
        'metadata',
        'evidence_files',
        'action_taken_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'evidence_files' => 'array',
        'action_taken_at' => 'datetime',
    ];

    // Relationships
    public function complianceReport(): BelongsTo
    {
        return $this->belongsTo(ComplianceReport::class);
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    // Scopes
    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByInvestigator($query, $investigatorId)
    {
        return $query->where('investigator_id', $investigatorId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('action_taken_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getActionTypeBadgeAttribute()
    {
        $badges = [
            'evidence_collected' => 'bg-blue-100 text-blue-800',
            'interview_conducted' => 'bg-purple-100 text-purple-800',
            'status_changed' => 'bg-yellow-100 text-yellow-800',
            'note_added' => 'bg-gray-100 text-gray-800',
            'escalated' => 'bg-red-100 text-red-800',
            'resolved' => 'bg-green-100 text-green-800',
        ];

        return $badges[$this->action_type] ?? 'bg-gray-100 text-gray-800';
    }

    // Constants
    const ACTION_TYPES = [
        'evidence_collected' => 'Evidence Collected',
        'interview_conducted' => 'Interview Conducted',
        'status_changed' => 'Status Changed',
        'note_added' => 'Note Added',
        'escalated' => 'Escalated',
        'resolved' => 'Resolved',
    ];
}