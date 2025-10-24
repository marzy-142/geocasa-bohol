<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\User;
use App\Models\Meeting;
use App\Notifications\MeetingScheduledNotification;
use App\Notifications\MeetingReminderNotification;
use App\Notifications\MeetingCancelledNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class MeetingSchedulingService
{
    protected CommunicationWorkflowService $workflowService;

    public function __construct(CommunicationWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function scheduleMeeting(
        Transaction $transaction,
        string $type,
        Carbon $scheduledAt,
        string $location,
        string $notes = null,
        array $attendees = [],
        int $reminderMinutes = 60
    ): array {
        try {
            $meeting = Meeting::create([
                'transaction_id' => $transaction->id,
                'client_id' => $transaction->client_id,
                'broker_id' => $transaction->broker_id,
                'type' => $type,
                'scheduled_at' => $scheduledAt,
                'location' => $location,
                'notes' => $notes,
                'status' => 'scheduled',
                'reminder_minutes' => $reminderMinutes,
                'attendees' => $attendees,
                'created_by' => auth()->id(),
            ]);

            // Notify all attendees
            $this->notifyMeetingScheduled($meeting, $transaction);

            // Schedule reminder
            $this->scheduleMeetingReminder($meeting, $transaction);

            // Update transaction engagement
            $this->updateTransactionEngagement($transaction, 'meeting_scheduled', [
                'meeting_type' => $type,
                'scheduled_at' => $scheduledAt->toISOString(),
            ]);

            Log::info('Meeting scheduled successfully', [
                'meeting_id' => $meeting->id,
                'transaction_id' => $transaction->id,
                'type' => $type,
                'scheduled_at' => $scheduledAt->toISOString(),
            ]);

            return [
                'success' => true,
                'meeting' => $meeting,
                'message' => 'Meeting scheduled successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to schedule meeting', [
                'transaction_id' => $transaction->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to schedule meeting. Please try again.',
            ];
        }
    }

    public function updateMeeting(Meeting $meeting, array $data): array
    {
        try {
            $originalScheduledAt = $meeting->scheduled_at;

            $meeting->update($data);

            // If time changed, reschedule reminder
            if (isset($data['scheduled_at']) && $data['scheduled_at'] != $originalScheduledAt) {
                $this->rescheduleMeetingReminder($meeting);
            }

            // Notify attendees of changes
            $this->notifyMeetingUpdated($meeting);

            Log::info('Meeting updated successfully', [
                'meeting_id' => $meeting->id,
                'changes' => array_keys($data),
            ]);

            return [
                'success' => true,
                'meeting' => $meeting,
                'message' => 'Meeting updated successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to update meeting', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to update meeting. Please try again.',
            ];
        }
    }

    public function cancelMeeting(Meeting $meeting, string $reason = null): array
    {
        try {
            $meeting->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancelled_by' => auth()->id(),
            ]);

            // Notify attendees of cancellation
            $this->notifyMeetingCancelled($meeting, $reason);

            // Cancel reminder
            $this->cancelMeetingReminder($meeting);

            Log::info('Meeting cancelled successfully', [
                'meeting_id' => $meeting->id,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Meeting cancelled successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to cancel meeting', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to cancel meeting. Please try again.',
            ];
        }
    }

    public function getTransactionMeetings(Transaction $transaction): array
    {
        $meetings = $transaction->meetings()
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return [
            'upcoming' => $meetings->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())
                ->values(),
            'completed' => $meetings->where('status', 'completed')->values(),
            'cancelled' => $meetings->where('status', 'cancelled')->values(),
            'total' => $meetings->count(),
        ];
    }

    public function getClientMeetingSchedule(Client $client): array
    {
        $meetings = Meeting::where('client_id', $client->id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->with(['transaction.property', 'broker'])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return [
            'upcoming' => $meetings,
            'this_week' => $meetings->whereBetween('scheduled_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->values(),
            'next_week' => $meetings->whereBetween('scheduled_at', [
                now()->addWeek()->startOfWeek(),
                now()->addWeek()->endOfWeek(),
            ])->values(),
        ];
    }

    protected function notifyMeetingScheduled(Meeting $meeting, Transaction $transaction): void
    {
        try {
            $attendees = $this->getMeetingAttendees($meeting);

            foreach ($attendees as $attendee) {
                $attendee->notify(new MeetingScheduledNotification($meeting, $transaction));
            }

            // Create workflow for meeting preparation
            $this->createMeetingPreparationWorkflow($meeting, $transaction);

        } catch (\Exception $e) {
            Log::error('Failed to send meeting scheduled notifications', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function notifyMeetingUpdated(Meeting $meeting): void
    {
        try {
            $attendees = $this->getMeetingAttendees($meeting);
            $transaction = $meeting->transaction;

            foreach ($attendees as $attendee) {
                $attendee->notify(new MeetingScheduledNotification($meeting, $transaction, 'updated'));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send meeting updated notifications', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function notifyMeetingCancelled(Meeting $meeting, string $reason = null): void
    {
        try {
            $attendees = $this->getMeetingAttendees($meeting);
            $transaction = $meeting->transaction;

            foreach ($attendees as $attendee) {
                $attendee->notify(new MeetingCancelledNotification($meeting, $transaction, $reason));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send meeting cancelled notifications', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function scheduleMeetingReminder(Meeting $meeting, Transaction $transaction): void
    {
        try {
            $reminderTime = $meeting->scheduled_at->subMinutes($meeting->reminder_minutes);

            if ($reminderTime->isFuture()) {
                // Schedule reminder job
                \App\Jobs\SendMeetingReminderJob::dispatch($meeting, $transaction)
                    ->delay($reminderTime);
            }

        } catch (\Exception $e) {
            Log::error('Failed to schedule meeting reminder', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function rescheduleMeetingReminder(Meeting $meeting): void
    {
        try {
            // Cancel existing reminder
            $this->cancelMeetingReminder($meeting);

            // Schedule new reminder
            if ($meeting->status === 'scheduled') {
                $this->scheduleMeetingReminder($meeting, $meeting->transaction);
            }

        } catch (\Exception $e) {
            Log::error('Failed to reschedule meeting reminder', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function cancelMeetingReminder(Meeting $meeting): void
    {
        try {
            // Cancel reminder job (implementation depends on queue system)
            // This would typically involve cancelling the scheduled job
            
        } catch (\Exception $e) {
            Log::error('Failed to cancel meeting reminder', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getMeetingAttendees(Meeting $meeting): array
    {
        $attendees = [];

        // Add client
        if ($meeting->client && $meeting->client->user) {
            $attendees[] = $meeting->client->user;
        }

        // Add broker
        if ($meeting->broker) {
            $attendees[] = $meeting->broker;
        }

        // Add additional attendees
        if (!empty($meeting->attendees)) {
            foreach ($meeting->attendees as $attendeeId) {
                $user = User::find($attendeeId);
                if ($user) {
                    $attendees[] = $user;
                }
            }
        }

        return array_unique($attendees, SORT_REGULAR);
    }

    protected function updateTransactionEngagement(Transaction $transaction, string $action, array $data): void
    {
        try {
            $engagement = \App\Models\ClientTransactionEngagement::where('transaction_id', $transaction->id)
                ->where('client_id', $transaction->client_id)
                ->first();

            if ($engagement) {
                $engagement->recordInteraction($action, array_merge($data, [
                    'timestamp' => now()->toISOString(),
                ]));
            }

        } catch (\Exception $e) {
            Log::error('Failed to update transaction engagement', [
                'transaction_id' => $transaction->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function createMeetingPreparationWorkflow(Meeting $meeting, Transaction $transaction): void
    {
        try {
            // Create workflow for meeting preparation reminders
            $workflowData = [
                'type' => 'meeting_preparation',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->type,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'location' => $meeting->location,
            ];

            $this->workflowService->createWorkflow(
                $transaction,
                'meeting_preparation',
                $workflowData,
                $meeting->scheduled_at->subHours(24) // 24 hours before meeting
            );

        } catch (\Exception $e) {
            Log::error('Failed to create meeting preparation workflow', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
