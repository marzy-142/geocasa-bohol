<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'client_id',
        'broker_id',
        'inquiry_id',
        'transaction_number',
        'transaction_type',
        'offered_price',
        'final_price',
        'commission_rate',
        'commission_amount',
        'status',
        'inquiry_date',
        'first_contact_date',
        'viewing_date',
        'offer_date',
        'acceptance_date',
        'contract_date',
        'closing_date',
        'finalized_date',
        'broker_notes',
        'documents',
        'status_history',
    ];

    protected $casts = [
        'offered_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'commission_amount' => 'decimal:2',
        'inquiry_date' => 'datetime',
        'first_contact_date' => 'datetime',
        'viewing_date' => 'datetime',
        'offer_date' => 'datetime',
        'acceptance_date' => 'datetime',
        'contract_date' => 'datetime',
        'closing_date' => 'datetime',
        'finalized_date' => 'datetime',
        'documents' => 'array',
        'status_history' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TXN-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($transaction) {
            if ($transaction->isDirty('status')) {
                $history = $transaction->status_history ?? [];
                $history[] = [
                    'status' => $transaction->status,
                    'changed_at' => now(),
                    'changed_by' => auth()->id(),
                ];
                $transaction->status_history = $history;
            }
        });
    }

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    // Scopes
    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['finalized', 'cancelled']);
    }

    public function scopeByBroker($query, $brokerId)
    {
        return $query->where('broker_id', $brokerId);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['negotiation', 'offer_accepted', 'contract_signed', 'due_diligence', 'financing', 'closing_preparation']);
    }

    // Accessors
    public function getIsFinalizedAttribute()
    {
        return $this->status === 'finalized';
    }

    public function getFormattedOfferedPriceAttribute()
    {
        return '$' . number_format($this->offered_price, 0);
    }

    public function getFormattedFinalPriceAttribute()
    {
        return $this->final_price ? '$' . number_format($this->final_price, 0) : null;
    }

    public function getFormattedCommissionAttribute()
    {
        return $this->commission_amount ? '$' . number_format($this->commission_amount, 2) : null;
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getDaysInProgressAttribute()
    {
        return $this->inquiry_date->diffInDays(now());
    }
}
