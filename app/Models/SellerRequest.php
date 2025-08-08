<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_name',
        'seller_email',
        'seller_phone',
        'seller_address',
        'property_title',
        'property_description',
        'asking_price',
        'property_area',
        'area_unit',
        'property_location',
        'property_address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'property_type',
        'features',
        'uploaded_images',
        'status',
        'admin_notes',
        'rejection_reason',
        'assigned_broker_id',
        'reviewed_by',
        'reviewed_at',
        'property_id',
        'listed_at',
    ];

    protected $casts = [
        'asking_price' => 'decimal:2',
        'property_area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'features' => 'array',
        'uploaded_images' => 'array',
        'reviewed_at' => 'datetime',
        'listed_at' => 'datetime',
    ];

    // Relationships
    public function assignedBroker()
    {
        return $this->belongsTo(User::class, 'assigned_broker_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAssignedTo($query, $brokerId)
    {
        return $query->where('assigned_broker_id', $brokerId);
    }

    // Accessors
    public function getFormattedAskingPriceAttribute()
    {
        return '$' . number_format($this->asking_price, 0);
    }

    public function getFormattedAreaAttribute()
    {
        return number_format($this->property_area, 1) . ' ' . $this->area_unit;
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    public function getIsListedAttribute()
    {
        return $this->status === 'listed';
    }
}
