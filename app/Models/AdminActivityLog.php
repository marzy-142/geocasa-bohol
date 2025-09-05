<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByTargetType($query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Accessors
    public function getActionBadgeAttribute()
    {
        $badges = [
            'user_created' => 'bg-green-100 text-green-800',
            'user_updated' => 'bg-blue-100 text-blue-800',
            'user_suspended' => 'bg-red-100 text-red-800',
            'user_reactivated' => 'bg-green-100 text-green-800',
            'user_approved' => 'bg-green-100 text-green-800',
            'user_rejected' => 'bg-red-100 text-red-800',
            'property_created' => 'bg-green-100 text-green-800',
            'property_updated' => 'bg-blue-100 text-blue-800',
            'property_deleted' => 'bg-red-100 text-red-800',
            'transaction_created' => 'bg-green-100 text-green-800',
            'transaction_updated' => 'bg-blue-100 text-blue-800',
            'seller_request_approved' => 'bg-green-100 text-green-800',
            'seller_request_rejected' => 'bg-red-100 text-red-800',
            'bulk_user_action' => 'bg-purple-100 text-purple-800',
            'system_settings_updated' => 'bg-yellow-100 text-yellow-800',
            'compliance_report_created' => 'bg-orange-100 text-orange-800',
            'compliance_report_updated' => 'bg-blue-100 text-blue-800',
        ];

        return $badges[$this->action] ?? 'bg-gray-100 text-gray-800';
    }

    public function getActionIconAttribute()
    {
        $icons = [
            'user_created' => 'UserPlusIcon',
            'user_updated' => 'UserIcon',
            'user_suspended' => 'XCircleIcon',
            'user_reactivated' => 'CheckCircleIcon',
            'user_approved' => 'CheckIcon',
            'user_rejected' => 'XMarkIcon',
            'property_created' => 'HomeIcon',
            'property_updated' => 'PencilIcon',
            'property_deleted' => 'TrashIcon',
            'transaction_created' => 'CurrencyDollarIcon',
            'transaction_updated' => 'PencilIcon',
            'seller_request_approved' => 'CheckIcon',
            'seller_request_rejected' => 'XMarkIcon',
            'bulk_user_action' => 'UsersIcon',
            'system_settings_updated' => 'CogIcon',
            'compliance_report_created' => 'ExclamationTriangleIcon',
            'compliance_report_updated' => 'PencilIcon',
        ];

        return $icons[$this->action] ?? 'DocumentIcon';
    }

    public function getFormattedDetailsAttribute()
    {
        if (!$this->details) {
            return null;
        }

        $formatted = [];
        foreach ($this->details as $key => $value) {
            if (is_array($value)) {
                $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . implode(', ', $value);
            } else {
                $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(' | ', $formatted);
    }

    // Constants
    const ACTIONS = [
        'user_created' => 'User Created',
        'user_updated' => 'User Updated',
        'user_suspended' => 'User Suspended',
        'user_reactivated' => 'User Reactivated',
        'user_approved' => 'User Approved',
        'user_rejected' => 'User Rejected',
        'property_created' => 'Property Created',
        'property_updated' => 'Property Updated',
        'property_deleted' => 'Property Deleted',
        'transaction_created' => 'Transaction Created',
        'transaction_updated' => 'Transaction Updated',
        'seller_request_approved' => 'Seller Request Approved',
        'seller_request_rejected' => 'Seller Request Rejected',
        'bulk_user_action' => 'Bulk User Action',
        'system_settings_updated' => 'System Settings Updated',
        'compliance_report_created' => 'Compliance Report Created',
        'compliance_report_updated' => 'Compliance Report Updated',
    ];

    const TARGET_TYPES = [
        'App\\Models\\User' => 'User',
        'App\\Models\\Property' => 'Property',
        'App\\Models\\Transaction' => 'Transaction',
        'App\\Models\\SellerRequest' => 'Seller Request',
        'App\\Models\\ComplianceReport' => 'Compliance Report',
    ];
}