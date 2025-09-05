<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ComplianceReport extends Model
{
    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'report_type',
        'severity',
        'status',
        'description',
        'evidence',
        'reported_by',
        'assigned_to',
        'admin_notes',
        'resolution_notes',
        'reported_at',
        'reviewed_at',
        'resolved_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function investigationLogs()
    {
        return $this->hasMany(InvestigationLog::class)->orderBy('action_taken_at', 'desc');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'dismissed' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getSeverityBadgeAttribute()
    {
        $badges = [
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->severity] ?? 'bg-gray-100 text-gray-800';
    }

    // Constants
    const REPORT_TYPES = [
        'spam' => 'Spam Content',
        'inappropriate_content' => 'Inappropriate Content',
        'fake_listing' => 'Fake Listing',
        'suspicious_activity' => 'Suspicious Activity',
        'policy_violation' => 'Policy Violation',
        'other' => 'Other',
    ];

    const SEVERITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    const STATUSES = [
        'pending' => 'Pending Review',
        'under_review' => 'Under Review',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];
}
