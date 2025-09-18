<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Register the fix broker verification command
// Remove this incorrect line - Laravel 11 auto-discovers commands in app/Console/Commands
// Artisan::command('fix:broker-verification {--dry-run : Show what would be updated without making changes}', FixBrokerVerificationStatus::class)
//     ->purpose('Fix verification status inconsistencies for approved brokers');

// Remove this line too - it's redundant
// Artisan::resolve(\App\Console\Commands\FixBrokerVerificationStatus::class);

// Schedule system monitoring
Schedule::command('system:monitor --critical')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('system:monitor')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule automated backups
Schedule::command('backup:run --cleanup')
    ->daily()
    ->at(config('backup.schedule.time', '02:00'))
    ->withoutOverlapping()
    ->runInBackground()
    ->when(function () {
        return config('backup.schedule.enabled', true);
    });

// Schedule backup cleanup (weekly)
Schedule::command('backup:run --cleanup')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule compliance monitoring
Schedule::command('compliance:monitor')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule daily comprehensive compliance check
Schedule::command('compliance:monitor --type=all')
    ->daily()
    ->at('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule follow-up reminders
Schedule::command('reminders:follow-up')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule daily follow-up reminder summary
Schedule::command('reminders:follow-up')
    ->daily()
    ->at('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule property renewal reminders (daily at 8:00 AM)
Schedule::command('property:send-renewal-reminders')
    ->daily()
    ->at('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule auto-expire listings (daily at 6:00 AM)
Schedule::command('property:auto-expire')
    ->daily()
    ->at('06:00')
    ->withoutOverlapping()
    ->runInBackground();
