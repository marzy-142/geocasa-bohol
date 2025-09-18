<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPropertyRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:send-renewal-reminders {--dry-run : Show what would be done without sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminders to brokers for properties expiring within 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting property renewal reminder process...');
        
        $isDryRun = $this->option('dry-run');
        
        // Get properties that need reminders
        $properties = Property::needsReminder()->with('broker')->get();
        
        if ($properties->isEmpty()) {
            $this->info('No properties need renewal reminders at this time.');
            return 0;
        }
        
        $this->info("Found {$properties->count()} properties that need renewal reminders.");
        
        $sentCount = 0;
        $errorCount = 0;
        
        foreach ($properties as $property) {
            try {
                if ($isDryRun) {
                    $this->line("[DRY RUN] Would send reminder for: {$property->title} (ID: {$property->id}) to {$property->broker->email}");
                    $sentCount++;
                } else {
                    $this->sendReminderEmail($property);
                    $property->markReminderSent();
                    $sentCount++;
                    $this->line("✓ Sent reminder for: {$property->title} to {$property->broker->email}");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("✗ Failed to send reminder for property {$property->id}: {$e->getMessage()}");
                Log::error('Property renewal reminder failed', [
                    'property_id' => $property->id,
                    'broker_id' => $property->broker_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Renewal reminder process completed.");
        $this->info("Sent: {$sentCount}, Errors: {$errorCount}");
        
        return 0;
    }
    
    private function sendReminderEmail(Property $property)
    {
        // For now, we'll use a simple mail notification
        // In a real implementation, you'd create a proper Mailable class
        $broker = $property->broker;
        $daysUntilExpiry = $property->days_until_expiry;
        
        $subject = "Property Listing Renewal Reminder - {$property->title}";
        $message = "Dear {$broker->name},\n\n";
        $message .= "Your property listing '{$property->title}' will expire in {$daysUntilExpiry} days.\n\n";
        $message .= "Property Details:\n";
        $message .= "- Title: {$property->title}\n";
        $message .= "- Location: {$property->full_address}\n";
        $message .= "- Price: {$property->formatted_total_price}\n";
        $message .= "- Expiry Date: {$property->expiry_date->format('M d, Y')}\n\n";
        $message .= "Please log in to your broker dashboard to renew this listing before it expires.\n\n";
        $message .= "Best regards,\nGeoCasa Bohol Team";
        
        Mail::raw($message, function ($mail) use ($broker, $subject) {
            $mail->to($broker->email, $broker->name)
                 ->subject($subject);
        });
    }
}
