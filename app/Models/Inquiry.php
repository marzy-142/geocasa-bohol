<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'inquiry_type',
        'property_id',
        'client_id',
        'assigned_broker_id',
        'user_id',
        'status',
        'contacted_at',
        'scheduled_at',
        'broker_notes',
        'broker_response',
        'responded_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

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
        return $this->belongsTo(User::class, 'assigned_broker_id');
    }

    public function propertyBroker()
    {
        return $this->hasOneThrough(User::class, Property::class, 'id', 'id', 'property_id', 'broker_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function complianceReports()
    {
        return $this->morphMany(ComplianceReport::class, 'reportable');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeForBroker($query, $brokerId)
    {
        return $query->where('assigned_broker_id', $brokerId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getIsNewAttribute()
    {
        return $this->status === 'new';
    }

    public function getIsRespondedAttribute()
    {
        return !is_null($this->responded_at);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }
}
