<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_new_inquiry',
        'email_transaction_updates',
        'email_messages',
        'email_seller_requests',
        'email_broker_assignments',
        'email_frequency',
        'phone_number',
        'sms_urgent_inquiries',
        'sms_transaction_milestones',
        'browser_notifications',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'email_new_inquiry' => 'boolean',
        'email_transaction_updates' => 'boolean',
        'email_messages' => 'boolean',
        'email_seller_requests' => 'boolean',
        'email_broker_assignments' => 'boolean',
        'sms_urgent_inquiries' => 'boolean',
        'sms_transaction_milestones' => 'boolean',
        'browser_notifications' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the notification preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notifications should be sent during quiet hours.
     */
    public function isQuietHour(): bool
    {
        // Return false if quiet hours are not set
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }
        
        $now = now()->format('H:i');
        $start = $this->quiet_hours_start->format('H:i');
        $end = $this->quiet_hours_end->format('H:i');

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        } else {
            // Quiet hours span midnight
            return $now >= $start || $now <= $end;
        }
    }

    /**
     * Check if current time is within quiet hours (alias for isQuietHour).
     */
    public function isWithinQuietHours()
    {
        return $this->isQuietHour();
    }

    /**
     * Check if email notifications are enabled for a specific type.
     */
    public function shouldSendEmail(string $type): bool
    {
        $field = 'email_' . $type;
        return $this->$field ?? false;
    }

    /**
     * Check if SMS notifications are enabled for a specific type.
     */
    public function shouldSendSms(string $type): bool
    {
        if (!$this->phone_number) {
            return false;
        }

        $field = 'sms_' . $type;
        return $this->$field ?? false;
    }
}
