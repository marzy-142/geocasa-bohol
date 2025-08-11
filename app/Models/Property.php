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
        'available', 'reserved', 'sold', 'under_negotiation', 'off_market'
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

    protected $fillable = [
        'title', 'slug', 'description', 'type', 'status', 'price_per_sqm', 'total_price',
        'address', 'municipality', 'barangay', 'lot_area_sqm', 'lot_area_hectares',
        'title_type', 'title_number', 'tax_declaration_number', 'coordinates_lat',
        'coordinates_lng', 'road_access', 'water_source', 'electricity_available',
        'internet_available', 'nearby_landmarks', 'zoning_classification',
        'images', 'documents', 'is_featured', 'broker_id', 'client_id'
    ];

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
    ];

    // Relationships
    public function broker()
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
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

    public function getFormattedPricePerSqmAttribute()
    {
        return '₱' . number_format($this->price_per_sqm, 2);
    }

    public function getFormattedAreaAttribute()
    {
        if ($this->lot_area_hectares >= 1) {
            return number_format($this->lot_area_hectares, 2) . ' hectares';
        }
        return number_format($this->lot_area_sqm, 0) . ' sqm';
    }

    public function getMainImageAttribute()
    {
        return $this->images && count($this->images) > 0 
            ? asset('storage/' . $this->images[0])
            : 'data:image/svg+xml;base64,' . base64_encode('
                <svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
                    <rect width="400" height="300" fill="#f3f4f6"/>
                    <text x="200" y="150" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="16">
                        No Image Available
                    </text>
                </svg>
            ');
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
}

## Completing STEP 4: GeoCasa Bohol Property Management System

### 1. Update Property Model for GeoCasa Bohol
