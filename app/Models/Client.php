<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'budget_min',
        'budget_max',
        'preferred_location',
        'preferred_area_min',
        'preferred_area_max',
        'preferred_features',
        'broker_id',
        'source',
        'notes',
        'status',
    ];

    /**
     * Scope for full-text search
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereRaw(
            "MATCH(name, email) AGAINST(? IN BOOLEAN MODE)",
            [$search]
        )->orWhere('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('phone', 'like', "%{$search}%");
    }

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'preferred_area_min' => 'decimal:2',
        'preferred_area_max' => 'decimal:2',
        'preferred_features' => 'array',
    ];

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for clients by broker
     */
    public function scopeByBroker($query, $brokerId)
    {
        return $query->where('broker_id', $brokerId);
    }

    // Constants for GeoCasa Bohol
    const SOURCES = ['inquiry', 'manual', 'referral', 'website'];
    const STATUSES = ['active', 'inactive', 'converted'];
    
    const PREFERRED_FEATURES = [
        'beachfront_access',
        'mountain_view',
        'road_access',
        'electricity_available',
        'water_source',
        'internet_available',
        'near_airport',
        'near_city_center',
        'agricultural_potential',
        'commercial_potential',
        'titled_property',
        'coconut_trees',
        'rice_field',
        'flat_terrain',
        'elevated_location'
    ];

    // Relationships
    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes - removed duplicate scopeActive() and scopeByBroker() methods
    public function scopeFromInquiries($query)
    {
        return $query->where('source', 'inquiry');
    }

    public function scopeWithBudgetRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('budget_max', '>=', $min);
        }
        if ($max) {
            $query->where('budget_min', '<=', $max);
        }
        return $query;
    }

    // Accessors
    public function getFormattedBudgetAttribute()
    {
        if ($this->budget_min && $this->budget_max) {
            return '₱' . number_format($this->budget_min, 0) . ' - ₱' . number_format($this->budget_max, 0);
        } elseif ($this->budget_min) {
            return 'Above ₱' . number_format($this->budget_min, 0);
        } elseif ($this->budget_max) {
            return 'Under ₱' . number_format($this->budget_max, 0);
        }
        return 'Not specified';
    }

    public function getFormattedPreferredAreaAttribute()
    {
        if ($this->preferred_area_min && $this->preferred_area_max) {
            return number_format($this->preferred_area_min, 1) . ' - ' . number_format($this->preferred_area_max, 1) . ' hectares';
        } elseif ($this->preferred_area_min) {
            return 'Above ' . number_format($this->preferred_area_min, 1) . ' hectares';
        } elseif ($this->preferred_area_max) {
            return 'Under ' . number_format($this->preferred_area_max, 1) . ' hectares';
        }
        return 'Not specified';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'converted' => 'blue',
            default => 'gray'
        };
    }

    public function getSourceLabelAttribute()
    {
        return match($this->source) {
            'inquiry' => 'Property Inquiry',
            'manual' => 'Manual Entry',
            'referral' => 'Referral',
            'website' => 'Website',
            default => ucfirst($this->source)
        };
    }

    // Business Logic Methods
    public function getTotalInquiriesAttribute()
    {
        return $this->inquiries()->count();
    }

    public function getActiveInquiriesAttribute()
    {
        return $this->inquiries()->whereIn('status', ['new', 'contacted', 'viewing_scheduled'])->count();
    }

    public function getTotalTransactionsAttribute()
    {
        return $this->transactions()->count();
    }

    public function getCompletedTransactionsAttribute()
    {
        return $this->transactions()->where('status', 'finalized')->count();
    }

    public function getLastActivityAttribute()
    {
        $lastInquiry = $this->inquiries()->latest()->first();
        $lastTransaction = $this->transactions()->latest()->first();
        
        if (!$lastInquiry && !$lastTransaction) {
            return $this->updated_at;
        }
        
        if (!$lastInquiry) {
            return $lastTransaction->updated_at;
        }
        
        if (!$lastTransaction) {
            return $lastInquiry->updated_at;
        }
        
        return $lastInquiry->updated_at->gt($lastTransaction->updated_at) 
            ? $lastInquiry->updated_at 
            : $lastTransaction->updated_at;
    }

    public function assignmentHistory()
    {
        return $this->hasMany(ClientAssignmentHistory::class)->orderBy('created_at', 'desc');
    }

    public function getLastAssignmentAttribute()
    {
        return $this->assignmentHistory()->first();
    }

    public function getAssignmentTimelineAttribute()
    {
        return $this->assignmentHistory()
            ->with(['broker:id,name', 'assignedBy:id,name'])
            ->get()
            ->map(function ($history) {
                return [
                    'date' => $history->created_at->format('M d, Y H:i'),
                    'action' => $history->action,
                    'broker' => $history->broker ? $history->broker->name : 'Unassigned',
                    'assigned_by' => $history->assignedBy->name,
                    'notes' => $history->notes,
                    'reason' => $history->assignment_reason
                ];
            });
    }
}
