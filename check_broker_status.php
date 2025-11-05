<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Current Broker Status Check\n";
echo "==========================\n\n";

// Check if there's a session or default to showing all brokers
$brokers = User::where('role', 'broker')->get();

foreach ($brokers as $broker) {
    echo "Broker: {$broker->name} (ID: {$broker->id})\n";
    echo "Email: {$broker->email}\n";
    echo "Is Approved: " . ($broker->is_approved ? 'Yes' : 'No') . "\n";
    echo "Application Status: " . ($broker->application_status ?? 'N/A') . "\n";
    echo "PRC Verified: " . ($broker->prc_verified ? 'Yes' : 'No') . "\n";
    echo "---\n";
}

// Check property creation permissions
echo "\nProperty Creation Test\n";
echo "======================\n";

foreach ($brokers as $broker) {
    $canCreate = $broker->role === 'broker' && $broker->is_approved;
    echo "Broker {$broker->name}: " . ($canCreate ? 'CAN' : 'CANNOT') . " create properties\n";
}