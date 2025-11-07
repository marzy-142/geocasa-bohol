<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Keep transaction-related statuses in sync automatically
        // Runs every 15 minutes; safe to run repeatedly; no overlapping runs
        $schedule
            ->command('reconcile:transactions')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        // Periodically ensure conversations have correct dynamic participants
        $schedule
            ->command('conversation:sync-participants')
            ->hourly()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
