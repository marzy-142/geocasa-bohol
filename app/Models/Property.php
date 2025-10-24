<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    // Land-focused property types for Bohol - matching reference specifications
    const TYPES = [
        'residential_lot', 
        'agricultural_land', 
        'commercial_lot', 
        'industrial_lot', 
        'beachfront', 
        'mountain_view', 
        'rice_field', 
        'coconut_plantation', 
        'subdivision_lot', 
        'titled_land', 
        'tax_declared'
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
        // Sale fields
        'sold_at', 'sold_price', 'sold_to_client_id', 'sold_via_transaction_id',
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
        'renewed_at' => 'datetime',
        // Sale casts
        'sold_at' => 'datetime',
        'sold_price' => 'decimal:2',
    ];

    // Boot method to auto-generate slugs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('title') && empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
        });
    }

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
    return '₱' . number_format((float) $this->total_price, 2);
    }

    public function getPriceAttribute()
    {
        return $this->total_price;
    }

    public function getFormattedAreaAttribute()
    {
        if ((float) $this->lot_area_hectares >= 1) {
            return number_format((float) $this->lot_area_hectares, 2) . ' hectares';
        }
        return number_format((float) $this->lot_area_sqm, 0) . ' sqm';
    }

    public function getFormattedPricePerSqmAttribute()
    {
    return '₱' . number_format((float) $this->price_per_sqm, 2);
    }

    public function getMainImageAttribute()
    {
        $images = $this->images;
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($images)) {
            $images = [];
        }

        // Extract first image candidate (supports string or object with common keys)
        $first = null;
        if (count($images) > 0) {
            $candidate = $images[0];
            if (is_array($candidate)) {
                // Common keys that might hold the image path/url
                foreach (['url', 'path', 'src', 'image', 'filename'] as $key) {
                    if (!empty($candidate[$key]) && is_string($candidate[$key])) {
                        $first = $candidate[$key];
                        break;
                    }
                }
            } elseif (is_string($candidate)) {
                $first = $candidate;
            }
        }

        if (is_string($first)) {
            $img = trim($first);

            if ($img !== '') {
                // Pass-through for absolute/data/blob URLs
                if (Str::startsWith($img, ['http://', 'https://', 'data:', 'blob:'])) {
                    return $img;
                }

                // If already a storage URL, normalize to have a single leading slash
                if (Str::startsWith($img, ['/storage/', 'storage/'])) {
                    return '/' . ltrim($img, '/');
                }

                // Normalize leading slashes and remove accidental 'public/' prefix
                $clean = ltrim($img, '/');
                if (Str::startsWith($clean, 'public/')) {
                    $clean = substr($clean, 7);
                }

                // If it already includes the expected directories, just prefix /storage
                if (Str::contains($clean, 'properties/virtual-tours/')) {
                    return '/storage/' . $clean;
                }
                if (Str::contains($clean, 'properties/images/')) {
                    return '/storage/' . $clean;
                }

                // Try a few common storage locations, prefer the first that exists
                $candidates = [
                    "public/properties/images/{$clean}" => "/storage/properties/images/{$clean}",
                    "public/{$clean}" => "/storage/{$clean}",
                ];

                foreach ($candidates as $diskPath => $publicUrl) {
                    if (Storage::exists($diskPath)) {
                        return $publicUrl;
                    }
                }

                // As a final fallback, assume standard images location
                return "/storage/properties/images/{$clean}";
            }
        }

        // Fallback to a default placeholder based on property type
        $placeholderColor = match($this->type) {
            'beachfront', 'residential_lot' => '#87CEEB',
            'agricultural_land' => '#90EE90',
            'commercial_lot' => '#DDA0DD',
            default => '#F0E68C'
        };
        
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="' . $placeholderColor . '" opacity="0.3"/>
    <rect width="100%" height="100%" fill="none" stroke="' . $placeholderColor . '" stroke-width="2"/>
    <circle cx="200" cy="120" r="30" fill="' . $placeholderColor . '" opacity="0.6"/>
    <rect x="170" y="160" width="60" height="40" fill="' . $placeholderColor . '" opacity="0.6" rx="5"/>
    <text x="200" y="230" font-family="Arial, sans-serif" font-size="12" font-weight="bold" text-anchor="middle" fill="#333">' . ucwords(str_replace('_', ' ', $this->type)) . '</text>
    <text x="200" y="250" font-family="Arial, sans-serif" font-size="10" text-anchor="middle" fill="#666">Property Image</text>
</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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
