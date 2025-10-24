<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestSmsNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTestSms extends Command
{
    protected $signature = 'sms:test {userId} {message=This is a GeoCasa Bohol test SMS.} {--to=}';

    protected $description = 'Send a test SMS via Twilio to the specified user using their notification preferences.';

    public function handle(): int
    {
    $userId = (int) $this->argument('userId');
    $message = (string) $this->argument('message');
    $overrideTo = $this->option('to');

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        // Determine destination number using routing in User model unless overridden
        $to = $overrideTo ?: $user->routeNotificationForTwilio();
        if (!$to) {
            $this->error('No phone number found. Provide one with --to=+63XXXXXXXXXX or set Notification Preferences / user phone field.');
            return self::FAILURE;
        }

        if ($overrideTo) {
            // Send using explicit routing
            Notification::route('twilio', $to)->notify(new TestSmsNotification($message));
        } else {
            // Send notification directly to the user (uses Twilio channel)
            $user->notify(new TestSmsNotification($message));
        }

        $this->info("Test SMS queued for sending to {$to}.");
        $this->line('Ensure your queue worker is running and TWILIO_* env vars are set.');

        return self::SUCCESS;
    }
}
