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
        // Client-centric fields
        'client_approvals',
        'client_feedback',
        'client_last_viewed',
        'client_satisfaction',
        'client_notes',
        'client_engagement_score',
        'requires_client_action',
        'client_action_deadline',
        'client_documents',
    ];

    protected $casts = [
        'offered_price' => 'decimal:2',
        'final_price' => 'decimal:2',
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
        // Client-centric casts
        'client_approvals' => 'array',
        'client_feedback' => 'array',
        'client_last_viewed' => 'datetime',
        'requires_client_action' => 'boolean',
        'client_action_deadline' => 'datetime',
        'client_documents' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TXN-' . strtoupper(Str::random(8));
            }
        });

        static::created(function ($transaction) {
            // Sync inquiry status on creation
            if ($transaction->inquiry) {
                $transaction->inquiry->update(['status' => 'in transaction']);
            }
        });

        static::updating(function ($transaction) {
            if ($transaction->isDirty('status')) {
                $history = $transaction->status_history ?? [];
                $history[] = [
                    'status' => $transaction->status,
                    'changed_at' => now(),
                    'changed_by' => auth()->id() ?? null,
                ];
                $transaction->status_history = $history;
            }
        });

        static::updated(function ($transaction) {
            // Sync inquiry status on status update
            if ($transaction->inquiry && $transaction->wasChanged('status')) {
                $inquiryStatus = null;
                switch ($transaction->status) {
                    case 'inquiry':
                    case 'initial_contact':
                    case 'property_viewing':
                    case 'offer_made':
                    case 'negotiation':
                    case 'offer_accepted':
                    case 'contract_signed':
                    case 'due_diligence':
                    case 'financing':
                    case 'closing_preparation':
                        $inquiryStatus = 'in transaction';
                        break;
                    case 'finalized':
                        $inquiryStatus = 'closed';
                        break;
                    case 'cancelled':
                        $inquiryStatus = 'not converted';
                        break;
                }
                if ($inquiryStatus) {
                    $transaction->inquiry->update(['status' => $inquiryStatus]);
                }
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

    public function communicationWorkflows()
    {
        return $this->morphMany(CommunicationWorkflow::class, 'workflowable');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
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
        return '$' . number_format((float) $this->offered_price, 0);
    }

    public function getFormattedFinalPriceAttribute()
    {
        return $this->final_price ? '$' . number_format((float) $this->final_price, 0) : null;
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
