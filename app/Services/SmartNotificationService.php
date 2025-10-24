<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\User;
use App\Models\NotificationPreference;
use App\Notifications\ClientTransactionUpdateNotification;
use App\Notifications\ClientDocumentRequestNotification;
use App\Notifications\ClientMeetingReminderNotification;
use App\Notifications\ClientMilestoneNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SmartNotificationService
{
    protected array $notificationQueue = [];
    protected array $batchSettings = [
        'max_batch_size' => 5,
        'batch_delay_minutes' => 15,
        'critical_immediate' => true,
    ];

    /**
     * Send transaction update notification with smart batching
     */
    public function sendTransactionUpdate(
        Transaction $transaction, 
        string $updateType, 
        array $data = []
    ): array {
        try {
            $client = $transaction->client;
            if (!$client || !$client->user) {
                return ['success' => false, 'error' => 'Client not found or has no user account.'];
            }

            $notificationData = [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'update_type' => $updateType,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ];

            $preferences = $this->getNotificationPreferences($client->user);
            
            // Check if update is critical
            if ($this->isCriticalUpdate($updateType)) {
                return $this->sendImmediateNotification($client->user, $notificationData, $preferences);
            }

            // Check notification preferences
            if (!$this->shouldSendNotification($client->user, $updateType, $preferences)) {
                return ['success' => true, 'action' => 'skipped', 'reason' => 'User preferences'];
            }

            // Check quiet hours
            if ($this->isWithinQuietHours($preferences)) {
                return $this->scheduleForLater($client->user, $notificationData, $preferences);
            }

            // Add to batch if not critical
            return $this->addToBatch($client->user, $notificationData, $preferences);

        } catch (\Exception $e) {
            Log::error('Smart notification service failed', [
                'transaction_id' => $transaction->id,
                'update_type' => $updateType,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send immediate notification for critical updates
     */
    private function sendImmediateNotification(
        User $user, 
        array $notificationData, 
        NotificationPreference $preferences
    ): array {
        try {
            $channels = $this->getChannelsForUser($user, $preferences, true);
            
            $user->notify(new ClientTransactionUpdateNotification(
                $notificationData['transaction_id'],
                $notificationData
            ));

            Log::info('Immediate notification sent', [
                'user_id' => $user->id,
                'update_type' => $notificationData['update_type'],
                'channels' => $channels
            ]);

            return [
                'success' => true,
                'action' => 'immediate',
                'channels' => $channels
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add notification to batch for later sending
     */
    private function addToBatch(
        User $user, 
        array $notificationData, 
        NotificationPreference $preferences
    ): array {
        $userId = $user->id;
        
        if (!isset($this->notificationQueue[$userId])) {
            $this->notificationQueue[$userId] = [];
        }

        $this->notificationQueue[$userId][] = [
            'data' => $notificationData,
            'preferences' => $preferences,
            'added_at' => now(),
        ];

        // Check if batch is ready to send
        if (count($this->notificationQueue[$userId]) >= $this->batchSettings['max_batch_size']) {
            return $this->sendBatch($user);
        }

        Log::info('Notification added to batch', [
            'user_id' => $user->id,
            'batch_size' => count($this->notificationQueue[$userId])
        ]);

        return [
            'success' => true,
            'action' => 'batched',
            'batch_size' => count($this->notificationQueue[$userId])
        ];
    }

    /**
     * Send batched notifications
     */
    private function sendBatch(User $user): array
    {
        try {
            $userId = $user->id;
            $notifications = $this->notificationQueue[$userId] ?? [];
            
            if (empty($notifications)) {
                return ['success' => true, 'action' => 'no_batch'];
            }

            // Clear the queue
            $this->notificationQueue[$userId] = [];

            // Combine notifications into a single message
            $combinedData = $this->combineNotificationData($notifications);
            $preferences = $notifications[0]['preferences'] ?? $this->getDefaultPreferences();

            $channels = $this->getChannelsForUser($user, $preferences, false);
            
            $user->notify(new ClientTransactionUpdateNotification(
                $combinedData['transaction_id'],
                $combinedData
            ));

            Log::info('Batch notification sent', [
                'user_id' => $user->id,
                'notification_count' => count($notifications),
                'channels' => $channels
            ]);

            return [
                'success' => true,
                'action' => 'batch_sent',
                'notification_count' => count($notifications),
                'channels' => $channels
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send batch notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Schedule notification for later (outside quiet hours)
     */
    private function scheduleForLater(
        User $user, 
        array $notificationData, 
        NotificationPreference $preferences
    ): array {
        try {
            $nextSendTime = $this->getNextSendTime($preferences);
            
            // Schedule the notification (you could use Laravel's job queue here)
            // For now, we'll just log it
            Log::info('Notification scheduled for later', [
                'user_id' => $user->id,
                'scheduled_for' => $nextSendTime,
                'update_type' => $notificationData['update_type']
            ]);

            return [
                'success' => true,
                'action' => 'scheduled',
                'scheduled_for' => $nextSendTime
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if update is critical and requires immediate notification
     */
    public function isCriticalUpdate(string $updateType): bool
    {
        $criticalUpdates = [
            'status_change',
            'offer_accepted',
            'contract_signed',
            'finalized',
            'cancelled',
            'approval_required',
            'deadline_approaching',
            'payment_due',
        ];

        return in_array($updateType, $criticalUpdates);
    }

    /**
     * Check if user wants to receive notifications for this update type
     */
    private function shouldSendNotification(
        User $user, 
        string $updateType, 
        NotificationPreference $preferences
    ): bool {
        $preferenceMap = [
            'status_change' => 'transaction_updates',
            'offer_accepted' => 'transaction_updates',
            'contract_signed' => 'transaction_updates',
            'finalized' => 'transaction_updates',
            'cancelled' => 'transaction_updates',
            'approval_required' => 'approvals',
            'document_request' => 'document_requests',
            'meeting_reminder' => 'meeting_reminders',
            'milestone_reached' => 'milestones',
        ];

        $preferenceKey = $preferenceMap[$updateType] ?? 'general_updates';
        
        return match($preferenceKey) {
            'transaction_updates' => $preferences->transaction_updates ?? true,
            'approvals' => $preferences->approvals ?? true,
            'document_requests' => $preferences->document_requests ?? true,
            'meeting_reminders' => $preferences->meeting_reminders ?? true,
            'milestones' => $preferences->milestones ?? true,
            'broker_assignments' => $preferences->broker_assignments ?? true,
            default => $preferences->general_updates ?? true,
        };
    }

    /**
     * Check if current time is within user's quiet hours
     */
    private function isWithinQuietHours(NotificationPreference $preferences): bool
    {
        if (!$preferences->quiet_hours_enabled) {
            return false;
        }

        $now = now();
        $quietStart = Carbon::parse($preferences->quiet_hours_start);
        $quietEnd = Carbon::parse($preferences->quiet_hours_end);

        // Handle quiet hours that span midnight
        if ($quietStart->greaterThan($quietEnd)) {
            return $now->greaterThanOrEqualTo($quietStart) || $now->lessThan($quietEnd);
        }

        return $now->greaterThanOrEqualTo($quietStart) && $now->lessThan($quietEnd);
    }

    /**
     * Get notification preferences for user
     */
    private function getNotificationPreferences(User $user): NotificationPreference
    {
        $preferences = NotificationPreference::where('user_id', $user->id)->first();
        
        if (!$preferences) {
            // Create default preferences
            $preferences = NotificationPreference::create([
                'user_id' => $user->id,
                'transaction_updates' => true,
                'approvals' => true,
                'document_requests' => true,
                'meeting_reminders' => true,
                'milestones' => true,
                'broker_assignments' => true,
                'general_updates' => true,
                'email_notifications' => true,
                'push_notifications' => true,
                'quiet_hours_enabled' => false,
                'quiet_hours_start' => '22:00',
                'quiet_hours_end' => '08:00',
                'batch_notifications' => true,
            ]);
        }

        return $preferences;
    }

    /**
     * Get default notification preferences
     */
    private function getDefaultPreferences(): NotificationPreference
    {
        return new NotificationPreference([
            'transaction_updates' => true,
            'approvals' => true,
            'document_requests' => true,
            'meeting_reminders' => true,
            'milestones' => true,
            'broker_assignments' => true,
            'general_updates' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'quiet_hours_enabled' => false,
            'batch_notifications' => true,
        ]);
    }

    /**
     * Get notification channels for user
     */
    private function getChannelsForUser(
        User $user, 
        NotificationPreference $preferences, 
        bool $isCritical = false
    ): array {
        $channels = ['database'];

        if ($isCritical || $preferences->email_notifications) {
            $channels[] = 'mail';
        }

        if ($preferences->push_notifications) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Combine multiple notification data into a single batch notification
     */
    private function combineNotificationData(array $notifications): array
    {
        $transactionId = null;
        $transactionNumber = null;
        $propertyTitle = null;
        $updates = [];

        foreach ($notifications as $notification) {
            $data = $notification['data'];
            
            if (!$transactionId) {
                $transactionId = $data['transaction_id'];
                $transactionNumber = $data['transaction_number'];
                $propertyTitle = $data['property_title'];
            }

            $updates[] = [
                'type' => $data['update_type'],
                'timestamp' => $data['timestamp'],
                'data' => $data['data'] ?? [],
            ];
        }

        return [
            'transaction_id' => $transactionId,
            'transaction_number' => $transactionNumber,
            'property_title' => $propertyTitle,
            'update_type' => 'batch_update',
            'updates' => $updates,
            'update_count' => count($updates),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get next send time outside quiet hours
     */
    private function getNextSendTime(NotificationPreference $preferences): Carbon
    {
        if (!$preferences->quiet_hours_enabled) {
            return now();
        }

        $now = now();
        $quietEnd = Carbon::parse($preferences->quiet_hours_end);

        // If we're currently in quiet hours, schedule for when they end
        if ($this->isWithinQuietHours($preferences)) {
            return $quietEnd;
        }

        return $now;
    }

    /**
     * Process pending batches (should be called periodically)
     */
    public function processPendingBatches(): void
    {
        foreach ($this->notificationQueue as $userId => $notifications) {
            if (empty($notifications)) {
                continue;
            }

            $user = User::find($userId);
            if (!$user) {
                unset($this->notificationQueue[$userId]);
                continue;
            }

            $oldestNotification = $notifications[0];
            $minutesSinceAdded = now()->diffInMinutes($oldestNotification['added_at']);

            // Send batch if it's been pending for too long
            if ($minutesSinceAdded >= $this->batchSettings['batch_delay_minutes']) {
                $this->sendBatch($user);
            }
        }
    }
}
