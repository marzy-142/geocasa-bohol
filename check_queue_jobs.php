<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== CHECKING FAILED QUEUE JOBS ===\n\n";

$failedJobs = DB::table('failed_jobs')
    ->orderBy('failed_at', 'desc')
    ->limit(3)
    ->get();

if ($failedJobs->isEmpty()) {
    echo "No failed jobs found.\n";
} else {
    echo "Found {$failedJobs->count()} recent failed jobs:\n\n";
    
    foreach ($failedJobs as $job) {
        echo "Job ID: {$job->id}\n";
        echo "UUID: {$job->uuid}\n";
        echo "Connection: {$job->connection}\n";
        echo "Queue: {$job->queue}\n";
        echo "Failed at: {$job->failed_at}\n";
        
        $payload = json_decode($job->payload, true);
        if (isset($payload['displayName'])) {
            echo "Job Type: {$payload['displayName']}\n";
        }
        
        // Show exception details
        echo "\nException:\n";
        $exceptionLines = explode("\n", $job->exception);
        echo "  " . $exceptionLines[0] . "\n";
        if (isset($exceptionLines[1])) {
            echo "  " . $exceptionLines[1] . "\n";
        }
        
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }
}

// Check pending jobs
echo "=== CHECKING PENDING QUEUE JOBS ===\n\n";

$pendingJobs = DB::table('jobs')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($pendingJobs->isEmpty()) {
    echo "No pending jobs in the queue.\n";
} else {
    echo "Found {$pendingJobs->count()} pending jobs:\n\n";
    
    foreach ($pendingJobs as $job) {
        echo "Job ID: {$job->id}\n";
        echo "Queue: {$job->queue}\n";
        echo "Attempts: {$job->attempts}\n";
        echo "Created: " . date('Y-m-d H:i:s', $job->created_at) . "\n";
        
        $payload = json_decode($job->payload, true);
        if (isset($payload['displayName'])) {
            echo "Job Type: {$payload['displayName']}\n";
        }
        
        echo "\n";
    }
}
