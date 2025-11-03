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

// Property renewal/auto-expire scheduling removed as the feature was deprecated

// Schedule broker application daily summary (daily at 9:00 AM)
Schedule::command('broker:daily-summary')
    ->daily()
    ->at('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Nightly reconcile: ensure properties of finalized transactions are marked as sold
Schedule::command('admin:fix-property-status')
    ->daily()
    ->at('00:30')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule saved search notifications (daily at 10:00 AM)
Schedule::command('searches:notify')
    ->daily()
    ->at('10:00')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule auto-archive sold properties (daily at 3:00 AM)
Schedule::command('properties:archive-sold')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();
