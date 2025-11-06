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
        'custom_property_type',
        'asking_price',
        'city',
        'province',
        'postal_code',
        'municipality',
        'barangay',
        'lot_area',
        'title_type',
        'title_number',
        'zoning_classification',
        'road_access',
        'water_source',
        'electricity_available',
        'internet_available',
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
        'coordinates_lat',
        'coordinates_lng',
        'nearby_landmarks',
    ];

    protected $casts = [
        'asking_price' => 'decimal:2',
        'price_expectation' => 'decimal:2',
        'lot_area' => 'decimal:2',
        'floor_area' => 'decimal:2',
        'property_area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'coordinates_lat' => 'decimal:8',
        'coordinates_lng' => 'decimal:8',
        'property_type' => \App\Casts\AsArrayWithoutSlashes::class,
        'features' => \App\Casts\AsArrayWithoutSlashes::class,
        'uploaded_images' => \App\Casts\AsArrayWithoutSlashes::class,
        'images' => \App\Casts\AsArrayWithoutSlashes::class,
        'property_documents' => \App\Casts\AsArrayWithoutSlashes::class,
        'documents' => \App\Casts\AsArrayWithoutSlashes::class,
        'ownership_documents' => \App\Casts\AsArrayWithoutSlashes::class,
        'marketing_consent' => 'boolean',
        'newsletter_consent' => 'boolean',
        'terms_accepted' => 'boolean',
        'road_access' => 'boolean',
        'water_source' => 'boolean',
        'electricity_available' => 'boolean',
        'internet_available' => 'boolean',
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
        $price = $this->asking_price !== null ? (float) $this->asking_price : 0.0;
        return '₱' . number_format($price, 0);
    }

    public function getFormattedAreaAttribute()
    {
        $area = $this->property_area !== null ? (float) $this->property_area : 0.0;
        $unit = $this->area_unit ?? 'sqm';
        return number_format($area, 1) . ' ' . $unit;
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
            'assigned' => 'indigo',
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
