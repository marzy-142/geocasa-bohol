<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientMeetingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Meeting $meeting;
    public array $reminderData;

    public function __construct(Meeting $meeting, array $reminderData)
    {
        $this->meeting = $meeting->load(['transaction.property', 'broker']);
        $this->reminderData = $reminderData;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $meetingType = ucwords(str_replace('_', ' ', $this->meeting->type));
        $hoursBefore = $this->reminderData['hours_before'];
        $subject = "Meeting Reminder: {$meetingType} - Transaction #{$this->reminderData['transaction_number']}";
        
        $greeting = "Hello {$notifiable->name},";
        $line1 = "This is a reminder that you have a **{$meetingType}** meeting in {$hoursBefore} hours.";
        $line2 = "**Property:** {$this->meeting->transaction->property->title}";
        $line3 = "**Date & Time:** " . \Carbon\Carbon::parse($this->reminderData['scheduled_at'])->format('M d, Y H:i A');
        $line4 = "**Location:** {$this->meeting->location}";

        $actionText = "View Meeting Details";
        $actionUrl = route('client.transactions.show', $this->meeting->transaction_id);

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($line1)
                    ->line($line2)
                    ->line($line3)
                    ->line($line4)
                    ->action($actionText, $actionUrl)
                    ->line('Please arrive on time and bring any required documents.')
                    ->salutation('Regards, The GeoCasa Bohol Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'transaction_id' => $this->reminderData['transaction_id'],
            'transaction_number' => $this->reminderData['transaction_number'],
            'property_title' => $this->reminderData['property_title'],
            'meeting_type' => $this->reminderData['meeting_type'],
            'scheduled_at' => $this->reminderData['scheduled_at'],
            'location' => $this->reminderData['location'],
            'hours_before' => $this->reminderData['hours_before'],
            'timestamp' => $this->reminderData['timestamp'],
            'message' => "Meeting reminder: {$this->reminderData['meeting_type']} in {$this->reminderData['hours_before']} hours",
            'link' => route('client.transactions.show', $this->reminderData['transaction_id']),
        ];
    }
}
