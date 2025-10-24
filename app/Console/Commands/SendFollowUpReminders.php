<?php

namespace App\Console\Commands;

use App\Models\Inquiry;
use App\Models\Message;
use App\Models\Conversation;
use App\Notifications\FollowUpReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:follow-up {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send follow-up reminders for unanswered inquiries and messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No notifications will be sent');
        }

        $this->info('Checking for follow-up reminders...');

        // Check for unanswered inquiries (broker hasn't responded within 24 hours)
        $this->checkUnansweredInquiries($dryRun);

        // Check for stale conversations (no response within 48 hours)
        $this->checkStaleConversations($dryRun);

        $this->info('Follow-up reminder check completed.');
    }

    /**
     * Check for inquiries that haven't been responded to by brokers
     */
    private function checkUnansweredInquiries($dryRun = false)
    {
        $unansweredInquiries = Inquiry::where('status', 'new')
            ->where('created_at', '<=', now()->subHours(24))
            ->whereNull('broker_response')
            ->with(['property.broker', 'client'])
            ->get();

        $this->info("Found {$unansweredInquiries->count()} unanswered inquiries");

        foreach ($unansweredInquiries as $inquiry) {
            if ($dryRun) {
                $this->line("Would send reminder to broker {$inquiry->property->broker->name} for inquiry #{$inquiry->id}");
            } else {
                try {
                    $inquiry->property->broker->notify(new FollowUpReminderNotification($inquiry, 'unanswered_inquiry'));
                    $this->line("Sent reminder to broker {$inquiry->property->broker->name} for inquiry #{$inquiry->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for inquiry #{$inquiry->id}: {$e->getMessage()}");
                    Log::error('Follow-up reminder failed', [
                        'inquiry_id' => $inquiry->id,
                        'broker_id' => $inquiry->property->broker->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Check for conversations with no recent activity
     */
    private function checkStaleConversations($dryRun = false)
    {
        $staleConversations = Conversation::whereHas('messages', function ($query) {
                $query->where('created_at', '<=', now()->subHours(48));
            })
            ->whereDoesntHave('messages', function ($query) {
                $query->where('created_at', '>', now()->subHours(48));
            })
            ->with(['participants', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get();

        $this->info("Found {$staleConversations->count()} stale conversations");

        foreach ($staleConversations as $conversation) {
            $lastMessage = $conversation->messages->first();
            if (!$lastMessage) continue;

            // Send reminder to the participant who didn't send the last message
            $otherParticipants = $conversation->participants
                ->where('id', '!=', $lastMessage->sender_id);

            foreach ($otherParticipants as $participant) {
                if ($dryRun) {
                    $this->line("Would send stale conversation reminder to {$participant->name} for conversation #{$conversation->id}");
                } else {
                    try {
                        $participant->notify(new FollowUpReminderNotification($conversation, 'stale_conversation'));
                        $this->line("Sent stale conversation reminder to {$participant->name} for conversation #{$conversation->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to send stale conversation reminder to {$participant->name}: {$e->getMessage()}");
                        Log::error('Stale conversation reminder failed', [
                            'conversation_id' => $conversation->id,
                            'participant_id' => $participant->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }
}