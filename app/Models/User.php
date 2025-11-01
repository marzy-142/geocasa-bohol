<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use App\Models\NotificationPreference;
use App\Notifications\CustomEmailVerificationNotification;

class User extends Authenticatable implements MustVerifyEmail
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
        'birthdate',
        'role',
        'is_approved',
        'approved_at',
    'approved_by',
    'prc_id',
    'prc_license_expiration',
        'prc_id_file',
        'prc_verified',
        'prc_verification_notes',
        'prc_verification_status',
        'prc_verification_result',
        'prc_verified_at', 
        'business_permit',
        'business_permit_file',
        'business_permit_verified',
        'business_permit_verification_notes',
        'additional_documents',
        'application_status',
        'rejection_reason',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
        'suspended_at',
        'suspended_until',
        'suspension_reason',
        'suspended_by',
        'brokerage_firm_name',
        'office_address',
        'office_contact_number',
        'years_experience',
        // Remove these unused fields:
        // 'company_address', - Use separate address fields instead
        // 'specialization', - Not used in frontend
        'city',
        'province', 
        'address',
        'postal_code',
        'phone',
        'terms_accepted',
        'privacy_policy_accepted',
        'information_certified',
        'prc_verification_consent',
        // Broker statistics
        'finalized_transactions_count',
        'last_sale_date',
        // Broker availability
        'availability_status',
        'max_concurrent_requests',
        'auto_manage_availability',
        // Broker directory settings
        'show_in_directory',
        'show_email_in_directory',
        'show_phone_in_directory',
        'accept_directory_inquiries',
        'bio',
        'specializations',
        'service_areas',
        'profile_image',
        'website',
        'facebook',
        'linkedin',
        // Account settings fields
        'avatar',
        'notification_preferences',
        'privacy_settings',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
    ];

    /**
     * Attributes that should be appended to model's array form.
     * Ensures prc_license_number is available in Inertia props.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'prc_license_number',
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
            'birthdate' => 'date',
            'is_approved' => 'boolean',
            'prc_verified' => 'boolean',
            'business_permit_verified' => 'boolean',
            'approved_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'suspended_at' => 'datetime',
            'suspended_until' => 'datetime',
            'prc_license_expiration' => 'date', // New cast
            'additional_documents' => 'array',
            'prc_verification_result' => 'array',
            'prc_verified_at' => 'datetime',
            'terms_accepted' => 'boolean',
            'privacy_policy_accepted' => 'boolean',
            'information_certified' => 'boolean', // New cast
            'prc_verification_consent' => 'boolean', // New cast
            // Broker availability casts
            'auto_manage_availability' => 'boolean',
            // Broker directory casts
            'show_in_directory' => 'boolean',
            'show_email_in_directory' => 'boolean',
            'show_phone_in_directory' => 'boolean',
            'accept_directory_inquiries' => 'boolean',
            'specializations' => 'array',
            'service_areas' => 'array',
            // Broker statistics casts
            'last_sale_date' => 'datetime',
            // Account settings casts
            'notification_preferences' => 'array',
            'privacy_settings' => 'array',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * Accessor: expose prc_id as prc_license_number for views/templates.
     */
    public function getPrcLicenseNumberAttribute(): ?string
    {
        return $this->prc_id;
    }

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
          ->orWhere('email', 'like', "%{$search}%");
    }

    /**
     * Scope for brokers only
     */
    public function scopeBrokers($query)
    {
        return $query->where('role', 'broker');
    }

    /**
     * Scope for approved brokers
     */
    public function scopeApproved($query)
    {
        return $query->where('application_status', 'approved');
    }

    /**
     * Scope for pending brokers
     */
    public function scopePending($query)
    {
        return $query->where('application_status', 'pending');
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
        return $query->where('role', 'broker')
                    ->where('is_approved', false)
                    ->whereIn('application_status', ['pending', 'under_review']);
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

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Get user's saved searches
     */
    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function isApproved(): bool
    {
        return $this->is_approved;
    }
    // Add these methods to the existing User model
    
    // Relationships
    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }
    
    public function suspendedUsers()
    {
        return $this->hasMany(User::class, 'suspended_by');
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function complianceReports()
    {
        return $this->morphMany(ComplianceReport::class, 'reportable');
    }

    public function reportedComplianceReports()
    {
        return $this->hasMany(ComplianceReport::class, 'reported_by');
    }

    public function assignedComplianceReports()
    {
        return $this->hasMany(ComplianceReport::class, 'assigned_to');
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('suspended_at')
                     ->orWhere('suspended_until', '<', now());
    }
    
    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_at')
                     ->where(function ($q) {
                         $q->whereNull('suspended_until')
                           ->orWhere('suspended_until', '>', now());
                     });
    }
    
    // Accessors
    public function getIsSuspendedAttribute()
    {
        if (!$this->suspended_at) {
            return false;
        }
        
        if (!$this->suspended_until) {
            return true; // Permanent suspension
        }
        
        return $this->suspended_until->isFuture();
    }
    
    public function getSuspensionStatusAttribute()
    {
        if (!$this->is_suspended) {
            return 'active';
        }
        
        if (!$this->suspended_until) {
            return 'permanently_suspended';
        }
        
        return 'temporarily_suspended';
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

    // Add the missing inquiries relationship
    public function inquiries()
    {
        return $this->hasManyThrough(Inquiry::class, Property::class, 'broker_id', 'property_id');
    }

    // Messaging relationships
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversations()
    {
        return Conversation::forUser($this->id);
    }

    public function getUnreadMessagesCount(): int
    {
        return Message::whereHas('conversation', function ($query) {
            $query->forUser($this->id);
        })
        ->where('sender_id', '!=', $this->id)
        ->whereNull('read_at')
        ->count();
    }

    public function assignedSellerRequests()
    {
        return $this->hasMany(SellerRequest::class, 'assigned_broker_id');
    }

    public function reviewedSellerRequests()
    {
        return $this->hasMany(SellerRequest::class, 'reviewed_by');
    }

    public function assignedInquiries()
    {
        return $this->hasMany(Inquiry::class, 'assigned_broker_id');
    }

    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function brokerRelationships()
    {
        return $this->hasMany(BrokerClientRelationship::class, 'broker_id');
    }

    public function communicationWorkflows()
    {
        return $this->hasMany(CommunicationWorkflow::class, 'broker_id');
    }

    public function performanceMetrics()
    {
        return $this->hasMany(BrokerPerformanceMetrics::class, 'broker_id');
    }

    public function mfaTokens()
    {
        return $this->hasMany(MfaToken::class);
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class);
    }

    /**
     * Get or create notification preferences for the user.
     */
    public function getNotificationPreferences()
    {
        return $this->notificationPreferences()->firstOrCreate(
            ['user_id' => $this->id],
            [
                'email_new_inquiry' => true,
                'email_transaction_updates' => true,
                'email_messages' => true,
                'email_seller_requests' => true,
                'email_frequency' => 'immediate',
                'sms_urgent_inquiries' => false,
                'sms_transaction_milestones' => false,
                'browser_notifications' => false,
                'quiet_hours_start' => '22:00:00',
                'quiet_hours_end' => '08:00:00',
            ]
        );
    }

    /**
     * Route notifications for the Twilio channel.
     * Prefer the phone number saved in notification preferences; fallback to the user's phone field.
     *
     * @param  \Illuminate\Notifications\Notification|null  $notification
     * @return string|null
     */
    public function routeNotificationForTwilio($notification = null)
    {
        try {
            $prefs = $this->getNotificationPreferences();
            if (!empty($prefs->phone_number)) {
                return $prefs->phone_number;
            }
        } catch (\Throwable $e) {
            // If preferences are not available yet, fall back gracefully
        }

        return $this->phone ?? null;
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

    public function canAccessBrokerDashboard(): bool
{
    return $this->role === 'broker' && 
           $this->is_approved && 
           $this->application_status === 'approved';
}

public function getBrokerStatusMessageAttribute(): string
{
    if ($this->role !== 'broker') {
        return '';
    }
    
    return match($this->application_status) {
        'pending' => 'Application submitted, awaiting review',
        'under_review' => 'Application is being reviewed by our team',
        'approved' => 'Application approved - welcome to GeoCasa Bohol!',
        'rejected' => 'Application rejected: ' . $this->rejection_reason,
        default => 'Unknown status'
    };
}

/**
 * Send the email verification notification.
 */
public function sendEmailVerificationNotification()
{
    $this->notify(new CustomEmailVerificationNotification);
}
}
