<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use App\Models\Inquiry;
use App\Services\InquiryLinkingService;

class DebugInquiries extends Command
{
    protected $signature = 'debug:inquiries {email?}';
    protected $description = 'Debug inquiry visibility issues';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('=== INQUIRY DEBUG REPORT ===');
        
        // Show total counts
        $this->info('Total inquiries: ' . Inquiry::count());
        $this->info('Total clients: ' . Client::count());
        $this->info('Total client users: ' . User::where('role', 'client')->count());
        
        $this->newLine();
        
        // Show inquiry details
        $this->info('=== INQUIRIES ===');
        $inquiries = Inquiry::with(['client', 'property'])->get();
        foreach ($inquiries as $inquiry) {
            $this->info("ID: {$inquiry->id}, Email: {$inquiry->email}, User ID: {$inquiry->user_id}, Client ID: {$inquiry->client_id}");
            if ($inquiry->property) {
                $this->info("  Property: {$inquiry->property->title}");
            }
        }
        
        $this->newLine();
        
        // Show client details
        $this->info('=== CLIENTS ===');
        $clients = Client::all();
        foreach ($clients as $client) {
            $this->info("ID: {$client->id}, Name: {$client->name}, Email: {$client->email}, User ID: {$client->user_id}");
        }
        
        $this->newLine();
        
        // Show users
        $this->info('=== CLIENT USERS ===');
        $users = User::where('role', 'client')->get();
        foreach ($users as $user) {
            $this->info("ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
        }
        
        // If email provided, test linking
        if ($email) {
            $this->newLine();
            $this->info("=== TESTING LINKING FOR: {$email} ===");
            
            $user = User::where('email', $email)->first();
            if ($user) {
                $linkingService = app(InquiryLinkingService::class);
                $result = $linkingService->linkExistingInquiriesToUser($user);
                $this->info('Linking result: ' . json_encode($result));
            } else {
                $this->error("User with email {$email} not found");
            }
        }
        
        return 0;
    }
}