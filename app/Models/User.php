<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_approved',
        'approved_at',
        'approved_by',
        'prc_id',
        'prc_id_file',
        'business_permit',
        'business_permit_file',
        'additional_documents',
        'application_status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'additional_documents' => 'array',
        ];
    }

    // Add new methods
    public function isPendingApproval(): bool
    {
        return $this->application_status === 'pending' || $this->application_status === 'under_review';
    }

    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }

    public function hasCredentials(): bool
    {
        return !empty($this->prc_id) || !empty($this->business_permit);
    }

    // Scopes for admin dashboard
    public function scopePendingApplications($query)
    {
        return $query->where('application_status', 'under_review');
    }

    public function scopeRejectedApplications($query)
    {
        return $query->where('application_status', 'rejected');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBroker(): bool
    {
        return $this->role === 'broker';
    }

    public function isPublic(): bool
    {
        return $this->role === 'client';
    }

    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    // Relationships
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    // Broker relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'broker_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'broker_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'broker_id');
    }

    public function assignedSellerRequests()
    {
        return $this->hasMany(SellerRequest::class, 'assigned_broker_id');
    }

    public function reviewedSellerRequests()
    {
        return $this->hasMany(SellerRequest::class, 'reviewed_by');
    }

    // Scopes
    public function scopePendingBrokers($query)
    {
        return $query->where('role', 'broker')->where('is_approved', false);
    }

    public function scopeApprovedBrokers($query)
    {
        return $query->where('role', 'broker')->where('is_approved', true);
    }

    // Broker statistics
    public function getFinalizedTransactionsCountAttribute()
    {
        return $this->transactions()->finalized()->count();
    }

    public function getActivePropertiesCountAttribute()
    {
        return $this->properties()->available()->count();
    }

    public function getActiveClientsCountAttribute()
    {
        return $this->clients()->active()->count();
    }

    public function getTotalCommissionEarnedAttribute()
    {
        return $this->transactions()->finalized()->sum('commission_amount');
    }
}
