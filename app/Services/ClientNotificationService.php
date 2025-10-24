<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\User;
use App\Models\Meeting;
use App\Notifications\ClientTransactionUpdateNotification;
use App\Notifications\ClientDocumentRequestNotification;
use App\Notifications\ClientMeetingReminderNotification;
use App\Notifications\ClientMilestoneNotification;
use App\Notifications\ClientEngagementNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ClientNotificationService
{
    protected CommunicationWorkflowService $workflowService;

    public function __construct(CommunicationWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function notifyTransactionUpdate(Transaction $transaction, string $updateType, array $data = []): array
    {
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

            // Send notification based on client preferences
            $this->sendNotificationByPreferences(
                $client->user,
                new ClientTransactionUpdateNotification($transaction, $notificationData),
                'transaction_updates'
            );

            // Update engagement metrics
            $this->updateClientEngagement($client, 'notification_received', [
                'notification_type' => 'transaction_update',
                'update_type' => $updateType,
            ]);

            Log::info('Client transaction update notification sent', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'update_type' => $updateType,
            ]);

            return ['success' => true, 'message' => 'Notification sent successfully.'];

        } catch (\Exception $e) {
            Log::error('Failed to send transaction update notification', [
                'transaction_id' => $transaction->id,
                'update_type' => $updateType,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send notification.'];
        }
    }

    public function notifyDocumentRequest(Transaction $transaction, array $requestedDocuments, string $deadline = null): array
    {
        try {
            $client = $transaction->client;
            if (!$client || !$client->user) {
                return ['success' => false, 'error' => 'Client not found or has no user account.'];
            }

            $notificationData = [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'requested_documents' => $requestedDocuments,
                'deadline' => $deadline,
                'timestamp' => now()->toISOString(),
            ];

            $this->sendNotificationByPreferences(
                $client->user,
                new ClientDocumentRequestNotification($transaction, $notificationData),
                'document_requests'
            );

            // Create workflow for document reminder
            if ($deadline) {
                $this->createDocumentReminderWorkflow($transaction, $requestedDocuments, $deadline);
            }

            Log::info('Client document request notification sent', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'document_count' => count($requestedDocuments),
            ]);

            return ['success' => true, 'message' => 'Document request notification sent successfully.'];

        } catch (\Exception $e) {
            Log::error('Failed to send document request notification', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send notification.'];
        }
    }

    public function notifyMeetingReminder(Meeting $meeting, int $hoursBefore = 24): array
    {
        try {
            $client = $meeting->client;
            if (!$client || !$client->user) {
                return ['success' => false, 'error' => 'Client not found or has no user account.'];
            }

            $notificationData = [
                'meeting_id' => $meeting->id,
                'transaction_id' => $meeting->transaction_id,
                'transaction_number' => $meeting->transaction->transaction_number,
                'property_title' => $meeting->transaction->property->title,
                'meeting_type' => $meeting->type,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'location' => $meeting->location,
                'hours_before' => $hoursBefore,
                'timestamp' => now()->toISOString(),
            ];

            $this->sendNotificationByPreferences(
                $client->user,
                new ClientMeetingReminderNotification($meeting, $notificationData),
                'meeting_reminders'
            );

            Log::info('Client meeting reminder notification sent', [
                'meeting_id' => $meeting->id,
                'client_id' => $client->id,
                'hours_before' => $hoursBefore,
            ]);

            return ['success' => true, 'message' => 'Meeting reminder sent successfully.'];

        } catch (\Exception $e) {
            Log::error('Failed to send meeting reminder notification', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send notification.'];
        }
    }

    public function notifyMilestoneAchieved(Transaction $transaction, string $milestone, array $data = []): array
    {
        try {
            $client = $transaction->client;
            if (!$client || !$client->user) {
                return ['success' => false, 'error' => 'Client not found or has no user account.'];
            }

            $notificationData = [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'milestone' => $milestone,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ];

            $this->sendNotificationByPreferences(
                $client->user,
                new ClientMilestoneNotification($transaction, $notificationData),
                'milestones'
            );

            // Update engagement metrics
            $this->updateClientEngagement($client, 'milestone_achieved', [
                'milestone' => $milestone,
            ]);

            Log::info('Client milestone notification sent', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'milestone' => $milestone,
            ]);

            return ['success' => true, 'message' => 'Milestone notification sent successfully.'];

        } catch (\Exception $e) {
            Log::error('Failed to send milestone notification', [
                'transaction_id' => $transaction->id,
                'milestone' => $milestone,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send notification.'];
        }
    }

    public function sendEngagementNotification(Client $client, string $type, array $data = []): array
    {
        try {
            if (!$client->user) {
                return ['success' => false, 'error' => 'Client has no user account.'];
            }

            $notificationData = array_merge($data, [
                'type' => $type,
                'timestamp' => now()->toISOString(),
            ]);

            $this->sendNotificationByPreferences(
                $client->user,
                new ClientEngagementNotification($client, $notificationData),
                'engagement'
            );

            Log::info('Client engagement notification sent', [
                'client_id' => $client->id,
                'type' => $type,
            ]);

            return ['success' => true, 'message' => 'Engagement notification sent successfully.'];

        } catch (\Exception $e) {
            Log::error('Failed to send engagement notification', [
                'client_id' => $client->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send notification.'];
        }
    }

    public function sendBulkNotifications(array $clients, string $notificationType, array $data = []): array
    {
        try {
            $successCount = 0;
            $failureCount = 0;

            foreach ($clients as $client) {
                $result = match ($notificationType) {
                    'transaction_update' => $this->notifyTransactionUpdate($client['transaction'], $client['update_type'], $client['data'] ?? []),
                    'document_request' => $this->notifyDocumentRequest($client['transaction'], $client['requested_documents'], $client['deadline'] ?? null),
                    'meeting_reminder' => $this->notifyMeetingReminder($client['meeting'], $client['hours_before'] ?? 24),
                    'milestone' => $this->notifyMilestoneAchieved($client['transaction'], $client['milestone'], $client['data'] ?? []),
                    'engagement' => $this->sendEngagementNotification($client['client'], $client['type'], $client['data'] ?? []),
                    default => ['success' => false, 'error' => 'Unknown notification type'],
                };

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            Log::info('Bulk notifications sent', [
                'total_clients' => count($clients),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'notification_type' => $notificationType,
            ]);

            return [
                'success' => true,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'message' => "Bulk notifications sent: {$successCount} successful, {$failureCount} failed.",
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send bulk notifications', [
                'notification_type' => $notificationType,
                'client_count' => count($clients),
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to send bulk notifications.'];
        }
    }

    protected function sendNotificationByPreferences(User $user, $notification, string $category): void
    {
        // Get user's notification preferences
        $preferences = $this->getUserNotificationPreferences($user, $category);

        // Determine notification channels based on preferences
        $channels = [];
        if ($preferences['email']) {
            $channels[] = 'mail';
        }
        if ($preferences['database']) {
            $channels[] = 'database';
        }
        if ($preferences['sms'] && $user->phone) {
            $channels[] = 'sms';
        }

        // Send notification through preferred channels
        if (!empty($channels)) {
            $notification->via = $channels;
            $user->notify($notification);
        }
    }

    protected function getUserNotificationPreferences(User $user, string $category): array
    {
        // Default preferences - can be customized per user
        $defaultPreferences = [
            'transaction_updates' => ['email' => true, 'database' => true, 'sms' => false],
            'document_requests' => ['email' => true, 'database' => true, 'sms' => true],
            'meeting_reminders' => ['email' => true, 'database' => true, 'sms' => true],
            'milestones' => ['email' => true, 'database' => true, 'sms' => false],
            'engagement' => ['email' => false, 'database' => true, 'sms' => false],
        ];

        // In a real implementation, this would fetch from user preferences table
        return $defaultPreferences[$category] ?? $defaultPreferences['transaction_updates'];
    }

    protected function updateClientEngagement(Client $client, string $action, array $data): void
    {
        try {
            // Update engagement metrics for all active transactions
            $activeTransactions = Transaction::where('client_id', $client->id)
                ->whereNotIn('status', ['finalized', 'cancelled'])
                ->get();

            foreach ($activeTransactions as $transaction) {
                $engagement = \App\Models\ClientTransactionEngagement::where('transaction_id', $transaction->id)
                    ->where('client_id', $client->id)
                    ->first();

                if ($engagement) {
                    $engagement->recordInteraction($action, array_merge($data, [
                        'timestamp' => now()->toISOString(),
                    ]));
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to update client engagement', [
                'client_id' => $client->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function createDocumentReminderWorkflow(Transaction $transaction, array $requestedDocuments, string $deadline): void
    {
        try {
            $deadlineDate = Carbon::parse($deadline);
            
            $workflowData = [
                'type' => 'document_reminder',
                'requested_documents' => $requestedDocuments,
                'deadline' => $deadlineDate->toISOString(),
            ];

            // Create reminder workflow 24 hours before deadline
            $reminderTime = $deadlineDate->subHours(24);
            if ($reminderTime->isFuture()) {
                $this->workflowService->createWorkflow(
                    $transaction,
                    'document_reminder',
                    $workflowData,
                    $reminderTime
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to create document reminder workflow', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
