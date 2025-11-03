<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Pending Brokers ===\n\n";

$pendingBrokers = DB::table('users')
    ->where('role', 'broker')
    ->where('is_approved', false)
    ->get(['id', 'name', 'email', 'application_status', 'is_approved', 'created_at']);

echo "Total pending brokers: " . $pendingBrokers->count() . "\n\n";

foreach ($pendingBrokers as $broker) {
    echo "ID: {$broker->id}\n";
    echo "Name: {$broker->name}\n";
    echo "Email: {$broker->email}\n";
    echo "Application Status: {$broker->application_status}\n";
    echo "Is Approved: " . ($broker->is_approved ? 'Yes' : 'No') . "\n";
    echo "Created At: {$broker->created_at}\n";
    echo "---\n\n";
}

echo "\n=== Scope Test (Using Model) ===\n\n";

$scopeResult = App\Models\User::pendingApplications()->get(['id', 'name', 'email', 'application_status']);

echo "Brokers from pendingApplications scope: " . $scopeResult->count() . "\n\n";

foreach ($scopeResult as $broker) {
    echo "ID: {$broker->id} | Name: {$broker->name} | Email: {$broker->email} | Status: {$broker->application_status}\n";
}
