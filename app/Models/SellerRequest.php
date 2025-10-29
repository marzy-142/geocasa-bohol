<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellerRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'client_id',
    'name',
    'email',
    'phone',
    'address',
        'property_title',
        'property_description',
        'property_type',
        'asking_price',
        'city',
        'province',
        'postal_code',
        'municipality',
        'barangay',
        'lot_area',
        'floor_area',
        'bedrooms',
        'bathrooms',
        'price_expectation',
        'description',
        'contact_name',
        'contact_email',
        'contact_phone',
        'features',
        'uploaded_images',
        'images',
        'property_documents',
        'documents',
        'ownership_documents',
        'availability',
        'urgency',
        'additional_notes',
        'marketing_consent',
        'newsletter_consent',
        'terms_accepted',
        'status',
        'admin_notes',
        'rejection_reason',
        'assigned_broker_id',
        'reviewed_by',
        'reviewed_at',
        'property_id',
        'listed_at',
        'submission_date',
        'assignment_method',
        'assigned_at',
        'wants_broker_selection',
    ];

    protected $casts = [
        'asking_price' => 'decimal:2',
        'price_expectation' => 'decimal:2',
        'lot_area' => 'decimal:2',
        'floor_area' => 'decimal:2',
        'property_area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'features' => 'array',
        'uploaded_images' => 'array',
        'images' => 'array',
        'property_documents' => 'array',
        'documents' => 'array',
        'ownership_documents' => 'array',
        'marketing_consent' => 'boolean',
        'newsletter_consent' => 'boolean',
        'terms_accepted' => 'boolean',
        'reviewed_at' => 'datetime',
        'listed_at' => 'datetime',
        'submission_date' => 'datetime',
        'assigned_at' => 'datetime',
        'wants_broker_selection' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

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

    public function scopeByPriceRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('asking_price', '>=', $min);
        }
        if ($max) {
            $query->where('asking_price', '<=', $max);
        }
        return $query;
    }

    // Accessors
    public function getFormattedAskingPriceAttribute()
    {
        return '₱' . number_format($this->asking_price, 0);
    }

    public function getFormattedAreaAttribute()
    {
        return number_format($this->property_area, 1) . ' ' . $this->area_unit;
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'under_review' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            'listed' => 'emerald'
        ];
        return $colors[$this->status] ?? 'gray';
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

    public function getCanBeEditedAttribute()
    {
        return in_array($this->status, ['pending', 'under_review']);
    }

    public function getCanBeConvertedAttribute()
    {
        return $this->status === 'approved' && !$this->property_id;
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setPropertyTitleAttribute($value)
    {
        $this->attributes['property_title'] = ucwords(strtolower(trim($value)));
    }

    // Helper methods
    public function getDaysOldAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getResponseTimeAttribute()
    {
        if ($this->reviewed_at) {
            return $this->created_at->diffInHours($this->reviewed_at);
        }
        return null;
    }
}
