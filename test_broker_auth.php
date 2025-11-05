<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Property Creation Authorization\n";
echo "=====================================\n\n";

// Get all brokers and test authorization
$brokers = User::where('role', 'broker')->get();

foreach ($brokers as $broker) {
    echo "Testing Broker: {$broker->name} (ID: {$broker->id})\n";
    echo "Email: {$broker->email}\n";
    echo "Is Approved: " . ($broker->is_approved ? 'Yes' : 'No') . "\n";
    echo "Application Status: " . ($broker->application_status ?? 'N/A') . "\n";
    
    // Test property creation authorization using the policy
    $canCreate = $broker->role === 'broker' && $broker->is_approved;
    echo "Can Create Properties (Policy): " . ($canCreate ? 'YES' : 'NO') . "\n";
    
    // Check if they would pass the middleware
    $passesMiddleware = $broker->role === 'broker' && $broker->is_approved;
    echo "Passes Broker Middleware: " . ($passesMiddleware ? 'YES' : 'NO') . "\n";
    
    echo "---\n";
}

// Test with a specific broker email if needed
$testEmail = 'maria@geocasabohol.com'; // Use Maria as test
$testBroker = User::where('email', $testEmail)->first();

if ($testBroker) {
    echo "\nDetailed Test for {$testBroker->name}:\n";
    echo "==================================\n";
    echo "Role: {$testBroker->role}\n";
    echo "Is Approved: " . ($testBroker->is_approved ? 'true' : 'false') . "\n";
    echo "Application Status: " . ($testBroker->application_status ?? 'null') . "\n";
    echo "PRC Verified: " . ($testBroker->prc_verified ? 'true' : 'false') . "\n";
    echo "Created At: {$testBroker->created_at}\n";
    echo "Updated At: {$testBroker->updated_at}\n";
    
    if ($testBroker->approved_at) {
        echo "Approved At: {$testBroker->approved_at}\n";
    }
    
    // Check specific conditions
    $roleCheck = $testBroker->role === 'broker';
    $approvalCheck = $testBroker->is_approved;
    
    echo "\nCondition Checks:\n";
    echo "Role is broker: " . ($roleCheck ? 'PASS' : 'FAIL') . "\n";
    echo "Is approved: " . ($approvalCheck ? 'PASS' : 'FAIL') . "\n";
    echo "Overall auth: " . ($roleCheck && $approvalCheck ? 'SHOULD WORK' : 'WILL FAIL') . "\n";
}