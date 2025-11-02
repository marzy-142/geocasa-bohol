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
        'subdivision_lot'
    ];

    const STATUSES = [
        'available', 
        'pending',           // Offer accepted, deal in progress
        'sold',              // Property sold
        'archived',          // Removed from public view
        'reserved', 
        'under_negotiation', 
        'off_market'
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
        'pending_at', 'sold_at', 'archived_at', 'sold_price', 'sold_to_client_id', 'sold_via_transaction_id',
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
        'pending_at' => 'datetime',
        'sold_at' => 'datetime',
        'archived_at' => 'datetime',
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
        'full_address',
        // New computed flag to indicate if property is under an active transaction
        'is_under_transaction',
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

    /**
     * Computed attribute: whether the property is currently under transaction.
     * True if status is explicitly under_negotiation or reserved, or if there is any
     * active (non-finalized, non-cancelled) transaction. Uses loaded counts/relations when available
     * to avoid extra queries; otherwise falls back to an existence check.
     */
    public function getIsUnderTransactionAttribute(): bool
    {
        // If status already indicates a transaction-like state
        if (in_array($this->status, ['under_negotiation', 'reserved'], true)) {
            return true;
        }

        // Prefer withCount alias if present to avoid N+1
        $activeCount = $this->getAttribute('active_transactions_count');
        if ($activeCount !== null) {
            return (int) $activeCount > 0;
        }

        // If transactions relation is loaded, filter in-memory
        if ($this->relationLoaded('transactions')) {
            return $this->transactions
                ->whereNotIn('status', ['finalized', 'cancelled'])
                ->count() > 0;
        }

        // Fallback: lightweight existence query
        return $this->transactions()
            ->whereNotIn('status', ['finalized', 'cancelled'])
            ->exists();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->whereIn('status', ['available', 'pending', 'reserved', 'under_negotiation']);
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

    public function getFormattedTypeAttribute()
    {
        return self::formatPropertyType($this->type);
    }

    public static function formatPropertyType($type)
    {
        $labels = [
            'residential_lot' => 'Residential Lot',
            'agricultural_land' => 'Agricultural Land',
            'commercial_lot' => 'Commercial Lot',
            'industrial_lot' => 'Industrial Lot',
            'beachfront' => 'Beachfront',
            'mountain_view' => 'Mountain View',
            'rice_field' => 'Rice Field',
            'coconut_plantation' => 'Coconut Plantation',
            'subdivision_lot' => 'Subdivision Lot',
        ];
        
        return $labels[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    public function getMainImageAttribute()
    {
        $images = $this->images;
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        if (is_array($images) && !empty($images)) {
            $imagePath = $images[0];

            // Handle cases where the image is an object with a path
            if (is_array($imagePath) && isset($imagePath['path'])) {
                $imagePath = $imagePath['path'];
            }

            if (is_string($imagePath) && $imagePath !== '') {
                // If it's already a full URL, return it.
                if (Str::startsWith($imagePath, ['http://', 'https://', 'data:'])) {
                    return $imagePath;
                }

                // Clean up common prefixes that might be mistakenly stored.
                $cleanPath = $imagePath;
                if (Str::startsWith($cleanPath, '/storage/')) {
                    $cleanPath = Str::after($cleanPath, '/storage/');
                }
                if (Str::startsWith($cleanPath, 'public/')) {
                    $cleanPath = Str::after($cleanPath, 'public/');
                }
                $cleanPath = ltrim($cleanPath, '/');

                // If the path doesn't already include a subdirectory, prepend the properties/images path
                if (!Str::contains($cleanPath, '/')) {
                    $cleanPath = 'properties/images/' . $cleanPath;
                }

                // Check if file exists and return relative URL (not absolute)
                // This ensures images work regardless of the domain being used
                if (Storage::disk('public')->exists($cleanPath)) {
                    return '/storage/' . $cleanPath;
                }
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

    public function getImagesAttribute($value)
    {
        // Get the raw images value
        $images = json_decode($value, true) ?? [];
        
        if (!is_array($images)) {
            return [];
        }

        // Transform each image path to a full URL
        return array_map(function($imagePath) {
            // Handle cases where the image is an object with a path
            if (is_array($imagePath) && isset($imagePath['path'])) {
                $imagePath = $imagePath['path'];
            }

            if (!is_string($imagePath) || $imagePath === '') {
                return null;
            }

            // If it's already a full URL, return it
            if (Str::startsWith($imagePath, ['http://', 'https://', 'data:'])) {
                return $imagePath;
            }

            // Clean up common prefixes
            $cleanPath = $imagePath;
            if (Str::startsWith($cleanPath, '/storage/')) {
                $cleanPath = Str::after($cleanPath, '/storage/');
            }
            if (Str::startsWith($cleanPath, 'public/')) {
                $cleanPath = Str::after($cleanPath, 'public/');
            }
            $cleanPath = ltrim($cleanPath, '/');

            // If the path doesn't already include a subdirectory, prepend the properties/images path
            if (!Str::contains($cleanPath, '/')) {
                $cleanPath = 'properties/images/' . $cleanPath;
            }

            // Return relative URL (not absolute) to work with any domain
            if (Storage::disk('public')->exists($cleanPath)) {
                return '/storage/' . $cleanPath;
            }

            return null;
        }, $images);
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

    // ==========================================
    // Property Status Management Methods
    // ==========================================

    /**
     * Mark property as pending (offer accepted)
     */
    public function markAsPending()
    {
        $this->update([
            'status' => 'pending',
            'pending_at' => now(),
        ]);
    }

    /**
     * Mark property as sold
     */
    public function markAsSold($soldPrice = null, $transactionId = null, $clientId = null)
    {
        $this->update([
            'status' => 'sold',
            'sold_at' => now(),
            'sold_price' => $soldPrice ?? $this->total_price,
            'sold_via_transaction_id' => $transactionId,
            'sold_to_client_id' => $clientId,
        ]);
    }

    /**
     * Archive property (remove from public view)
     */
    public function archive()
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    /**
     * Check if property should be auto-archived (90 days after sold)
     */
    public function shouldBeArchived()
    {
        return $this->status === 'sold' 
            && $this->sold_at 
            && $this->sold_at->addDays(90)->isPast();
    }

    /**
     * Check if property has an assigned client (active transaction)
     * Returns true if property status indicates it's no longer available for new inquiries
     */
    public function hasAssignedClient()
    {
        // Property has an assigned client if it's in any of these statuses
        return in_array($this->status, [
            'reserved',           // Property is reserved for a client
            'under_negotiation',  // Property is under negotiation with a client
            'sold',              // Property has been sold
            'pending'            // Offer accepted, deal in progress
        ]);
    }

    /**
     * Get a user-friendly message explaining why property is unavailable for new inquiries
     */
    public function getUnavailableReasonAttribute()
    {
        if (!$this->hasAssignedClient()) {
            return null;
        }

        $messages = [
            'reserved' => 'This property is reserved and cannot accept new inquiries.',
            'under_negotiation' => 'This property is under negotiation with a client and cannot accept new inquiries.',
            'sold' => 'This property has been sold and cannot accept new inquiries.',
            'pending' => 'This property has an accepted offer and cannot accept new inquiries.'
        ];

        return $messages[$this->status] ?? 'This property is not available for new inquiries.';
    }

    /**
     * Get days since sold
     */
    public function getDaysSinceSoldAttribute()
    {
        if (!$this->sold_at) {
            return null;
        }
        
        return now()->diffInDays($this->sold_at);
    }

    // ==========================================
    // Query Scopes for Status Filtering
    // ==========================================

    /**
     * Scope: Only available properties
     */

    /**
     * Scope: Only pending properties
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Only sold properties
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    /**
     * Scope: Only archived properties
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope: Active listings (available + pending)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['available', 'pending']);
    }

    /**
     * Scope: Public listings (exclude archived)
     */
    public function scopePublic($query)
    {
        return $query->whereNotIn('status', ['archived', 'off_market']);
    }

    /**
     * Scope: Recently sold (within last 6 months)
     */
    public function scopeRecentlySold($query)
    {
        return $query->where('status', 'sold')
            ->where('sold_at', '>=', now()->subMonths(6));
    }

    /**
     * Scope: Should be archived (sold > 90 days ago)
     */
    public function scopeShouldBeArchived($query)
    {
        return $query->where('status', 'sold')
            ->where('sold_at', '<=', now()->subDays(90));
    }

    // ==========================================
    // Status Check Methods
    // ==========================================

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSold()
    {
        return $this->status === 'sold';
    }

    public function isArchived()
    {
        return $this->status === 'archived';
    }

    public function isActive()
    {
        return in_array($this->status, ['available', 'pending']);
    }
}
