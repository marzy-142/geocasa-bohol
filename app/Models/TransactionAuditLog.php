<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionAuditLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'user_id',
        'user_role',
        'user_name',
        'reason',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // We only use created_at

    /**
     * Get the transaction that owns the audit log.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by action type
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user role
     */
    public function scopeByUserRole($query, string $role)
    {
        return $query->where('user_role', $role);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action description
     */
    public function getActionDescriptionAttribute(): string
    {
        return match($this->action) {
            'status_change' => "Status changed from '{$this->old_value}' to '{$this->new_value}'",
            'field_update' => "Field '{$this->field_name}' updated",
            'approval_requested' => 'Client approval requested',
            'approval_responded' => 'Client approval responded',
            'document_uploaded' => 'Document uploaded',
            'transaction_created' => 'Transaction created',
            'transaction_updated' => 'Transaction updated',
            default => ucwords(str_replace('_', ' ', $this->action))
        };
    }

    /**
     * Get formatted old value
     */
    public function getFormattedOldValueAttribute(): string
    {
        if ($this->field_name === 'status') {
            return ucwords(str_replace('_', ' ', $this->old_value ?? ''));
        }
        
        return $this->old_value ?? 'N/A';
    }

    /**
     * Get formatted new value
     */
    public function getFormattedNewValueAttribute(): string
    {
        if ($this->field_name === 'status') {
            return ucwords(str_replace('_', ' ', $this->new_value ?? ''));
        }
        
        return $this->new_value ?? 'N/A';
    }
}
