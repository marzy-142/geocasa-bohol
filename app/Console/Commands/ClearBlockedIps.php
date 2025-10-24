<?php

namespace App\Console\Commands;

use App\Models\LoginAttempt;
use Illuminate\Console\Command;

class ClearBlockedIps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:clear-blocked-ips {--all : Clear all blocked IPs regardless of expiry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear blocked IP addresses from the login attempts table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = LoginAttempt::whereNotNull('blocked_until');
        
        if (!$this->option('all')) {
            $query->where('blocked_until', '>', now());
        }

        $blockedCount = $query->count();

        if ($blockedCount === 0) {
            $this->info('No blocked IPs found.');
            return;
        }

        $this->info("Found {$blockedCount} blocked IP(s).");

        if ($this->confirm('Do you want to clear all blocked IPs?')) {
            $cleared = $query->update(['blocked_until' => null]);
            $this->info("Successfully cleared {$cleared} blocked IP(s).");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}





