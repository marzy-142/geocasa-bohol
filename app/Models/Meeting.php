<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'client_id',
        'broker_id',
        'type',
        'title',
        'scheduled_at',
        'scheduled_date', // Keep for backward compatibility
        'location',
        'notes',
        'status',
        'reminder_minutes',
        'attendees',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'attendees' => 'array',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_minutes' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByBroker($query, $brokerId)
    {
        return $query->where('broker_id', $brokerId);
    }

    // Accessors
    public function getIsUpcomingAttribute()
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isFuture();
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isPast();
    }

    public function getFormattedScheduledAtAttribute()
    {
        return $this->scheduled_at->format('M d, Y H:i A');
    }

    public function getDurationAttribute()
    {
        // Default 1 hour duration, can be customized
        return 60;
    }
}