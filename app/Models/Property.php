<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    // Land-focused property types for Bohol
    const TYPES = [
        'residential_lot', 'agricultural_land', 'commercial_lot', 'industrial_lot', 
        'beachfront', 'mountain_view', 'rice_field', 'coconut_plantation', 
        'subdivision_lot', 'titled_land', 'tax_declared'
    ];

    const STATUSES = [
        'available', 'reserved', 'sold', 'under_negotiation', 'off_market', 'pending_renewal'
    ];

    // Bohol-specific locations/municipalities
    const BOHOL_MUNICIPALITIES = [
        'Tagbilaran City', 'Baclayon', 'Balilihan', 'Batuan', 'Bilar', 'Buenavista',
        'Calape', 'Candijay', 'Carmen', 'Catigbian', 'Clarin', 'Corella', 'Cortes',
        'Dagohoy', 'Danao', 'Dauis', 'Dimiao', 'Duero', 'Garcia Hernandez',
        'Guindulman', 'Inabanga', 'Jagna', 'Jetafe', 'Lila', 'Loay', 'Loboc',
        'Loon', 'Mabini', 'Maribojoc', 'Panglao', 'Pilar', 'President Carlos P. Garcia',
        'Sagbayan', 'San Isidro', 'San Miguel', 'Sevilla', 'Sierra Bullones',
        'Sikatuna', 'Talibon', 'Trinidad', 'Tubigon', 'Ubay', 'Valencia', 'Well'
    ];

    // Add these fields to the fillable array and casts
    // Add these to the existing fillable array
    protected $fillable = [
        'title', 'slug', 'description', 'type', 'status', 'price_per_sqm', 'total_price',
        'address', 'municipality', 'barangay', 'lot_area_sqm', 'lot_area_hectares',
        'title_type', 'title_number', 'tax_declaration_number', 'coordinates_lat',
        'coordinates_lng', 'road_access', 'water_source', 'electricity_available',
        'internet_available', 'nearby_landmarks', 'zoning_classification',
        'images', 'documents', 'is_featured', 'broker_id', 'client_id',
        // Virtual tour fields
        'virtual_tour_images', 'has_virtual_tour', 'gis_data', 'tour_hotspots',
        // Expiry tracking fields
        'last_updated_at', 'expiry_date', 'reminder_sent_at', 'renewal_required', 'renewed_at',
    ];
    
    // Add these to the existing casts array
    protected $casts = [
        'price_per_sqm' => 'decimal:2',
        'total_price' => 'decimal:2',
        'lot_area_sqm' => 'decimal:2',
        'lot_area_hectares' => 'decimal:4',
        'coordinates_lat' => 'decimal:8',
        'coordinates_lng' => 'decimal:8',
        'road_access' => 'boolean',
        'water_source' => 'boolean',
        'electricity_available' => 'boolean',
        'internet_available' => 'boolean',
        'images' => 'array',
        'documents' => 'array',
        'nearby_landmarks' => 'array',
        'is_featured' => 'boolean',
        'gis_data' => 'array',
        'virtual_tour_images' => 'array',
        'has_virtual_tour' => 'boolean',
        'tour_hotspots' => 'array',
        // Expiry tracking casts
        'last_updated_at' => 'datetime',
        'expiry_date' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'renewal_required' => 'boolean',
        'renewed_at' => 'datetime'
    ];

    // Append computed attributes to JSON serialization
    protected $appends = [
        'formatted_total_price',
        'formatted_area',
        'formatted_price_per_sqm',
        'main_image',
        'google_maps_link',
        'full_address'
    ];

    // Relationships
    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function complianceReports()
    {
        return $this->morphMany(ComplianceReport::class, 'reportable');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->whereNotIn('status', ['pending_renewal']);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                    ->where('status', '!=', 'pending_renewal');
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('expiry_date', '<=', now()->addDays(7))
                    ->where('status', 'available')
                    ->where(function($q) {
                        $q->whereNull('reminder_sent_at')
                          ->orWhere('reminder_sent_at', '<', now()->subDays(7));
                    });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePendingRenewal($query)
    {
        return $query->where('status', 'pending_renewal');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByMunicipality($query, $municipality)
    {
        return $query->where('municipality', $municipality);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('total_price', [$min, $max]);
    }

    public function scopeAreaRange($query, $minSqm, $maxSqm)
    {
        return $query->whereBetween('lot_area_sqm', [$minSqm, $maxSqm]);
    }

    public function scopeWithUtilities($query)
    {
        return $query->where('electricity_available', true)
                    ->where('water_source', true);
    }

    public function scopeBeachfront($query)
    {
        return $query->where('type', 'beachfront');
    }

    // Accessors
    public function getFormattedTotalPriceAttribute()
    {
        return '₱' . number_format($this->total_price, 2);
    }

    public function getPriceAttribute()
    {
        return $this->total_price;
    }

    public function getFormattedAreaAttribute()
    {
        if ($this->lot_area_hectares >= 1) {
            return number_format($this->lot_area_hectares, 2) . ' hectares';
        }
        return number_format($this->lot_area_sqm, 0) . ' sqm';
    }

    public function getFormattedPricePerSqmAttribute()
    {
        return '₱' . number_format($this->price_per_sqm, 2);
    }

    public function getMainImageAttribute()
    {
        return $this->images && count($this->images) > 0 
            ? asset('storage/' . $this->images[0])
            : 'data:image/svg+xml;base64,' . base64_encode('...');
    }

    public function getGoogleMapsLinkAttribute()
    {
        if ($this->coordinates_lat && $this->coordinates_lng) {
            return "https://www.google.com/maps?q={$this->coordinates_lat},{$this->coordinates_lng}";
        }
        return null;
    }

    public function getFullAddressAttribute()
    {
        return trim("{$this->address}, {$this->barangay}, {$this->municipality}, Bohol, Philippines");
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Expiry Management Methods
    public function setExpiryDate($days = 90)
    {
        $this->update([
            'expiry_date' => now()->addDays($days),
            'last_updated_at' => now(),
            'renewal_required' => false,
            'reminder_sent_at' => null
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'status' => 'pending_renewal',
            'renewal_required' => true
        ]);
    }

    public function renewListing($days = 90)
    {
        $this->update([
            'status' => 'available',
            'expiry_date' => now()->addDays($days),
            'last_updated_at' => now(),
            'renewal_required' => false,
            'reminder_sent_at' => null
        ]);
    }

    public function markReminderSent()
    {
        $this->update(['reminder_sent_at' => now()]);
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function needsReminder()
    {
        if (!$this->expiry_date || $this->status !== 'available') {
            return false;
        }

        $reminderThreshold = now()->addDays(7);
        $lastReminderThreshold = now()->subDays(7);

        return $this->expiry_date <= $reminderThreshold && 
               (!$this->reminder_sent_at || $this->reminder_sent_at < $lastReminderThreshold);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }
}
