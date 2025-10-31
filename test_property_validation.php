<?php

/**
 * Quick Manual Test Script for Property Assignment Validation
 * 
 * Run this in Tinker: php artisan tinker
 * Then copy and paste this code
 */

use App\Models\Property;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Client;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Property Assignment Validation - Quick Test              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Check hasAssignedClient() method
echo "Test 1: Property::hasAssignedClient() Method\n";
echo "─────────────────────────────────────────────\n";

$availableProperty = Property::where('status', 'available')->first();
$reservedProperty = Property::where('status', 'reserved')->first();

if (!$availableProperty) {
    echo "⚠️  No 'available' property found. Creating one...\n";
    $broker = User::where('role', 'broker')->first();
    $availableProperty = Property::factory()->create([
        'status' => 'available',
        'broker_id' => $broker->id
    ]);
}

if (!$reservedProperty) {
    echo "⚠️  No 'reserved' property found. Creating one...\n";
    $broker = User::where('role', 'broker')->first();
    $reservedProperty = Property::factory()->create([
        'status' => 'reserved',
        'broker_id' => $broker->id
    ]);
}

echo "Available Property (ID: {$availableProperty->id}):\n";
echo "  Status: {$availableProperty->status}\n";
echo "  Has Assigned Client: " . ($availableProperty->hasAssignedClient() ? 'YES ❌' : 'NO ✅') . "\n";
echo "  Unavailable Reason: " . ($availableProperty->unavailable_reason ?? 'null ✅') . "\n\n";

echo "Reserved Property (ID: {$reservedProperty->id}):\n";
echo "  Status: {$reservedProperty->status}\n";
echo "  Has Assigned Client: " . ($reservedProperty->hasAssignedClient() ? 'YES ✅' : 'NO ❌') . "\n";
echo "  Unavailable Reason: " . ($reservedProperty->unavailable_reason ?? 'null ❌') . "\n\n";

// Test 2: Test all unavailable statuses
echo "\nTest 2: All Property Statuses\n";
echo "─────────────────────────────────────────────\n";

$testStatuses = [
    'available' => false,
    'reserved' => true,
    'under_negotiation' => true,
    'sold' => true,
    'pending' => true,
    'archived' => false,
    'off_market' => false,
];

foreach ($testStatuses as $status => $shouldBlock) {
    $property = Property::factory()->make(['status' => $status]);
    $hasClient = $property->hasAssignedClient();
    $expected = $shouldBlock ? 'YES' : 'NO';
    $actual = $hasClient ? 'YES' : 'NO';
    $match = ($hasClient === $shouldBlock) ? '✅' : '❌';
    
    echo sprintf("%-20s | Expected: %-3s | Actual: %-3s | %s\n", 
        $status, $expected, $actual, $match);
}

// Test 3: Check unavailable reason messages
echo "\nTest 3: Unavailable Reason Messages\n";
echo "─────────────────────────────────────────────\n";

$reasonStatuses = ['reserved', 'under_negotiation', 'sold', 'pending'];

foreach ($reasonStatuses as $status) {
    $property = Property::factory()->make(['status' => $status]);
    $reason = $property->unavailable_reason;
    $hasReason = !empty($reason);
    
    echo "{$status}:\n";
    echo "  Has Reason: " . ($hasReason ? 'YES ✅' : 'NO ❌') . "\n";
    echo "  Message: {$reason}\n\n";
}

// Test 4: Real inquiry test (if possible)
echo "\nTest 4: Real Inquiry Test\n";
echo "─────────────────────────────────────────────\n";

$inquiry = Inquiry::with('property')->first();

if ($inquiry && $inquiry->property) {
    echo "Inquiry ID: {$inquiry->id}\n";
    echo "Property: {$inquiry->property->title}\n";
    echo "Property Status: {$inquiry->property->status}\n";
    echo "Has Assigned Client: " . ($inquiry->property->hasAssignedClient() ? 'YES' : 'NO') . "\n";
    
    if ($inquiry->property->hasAssignedClient()) {
        echo "⚠️  This inquiry should show warning in UI\n";
        echo "Reason: {$inquiry->property->unavailable_reason}\n";
    } else {
        echo "✅ This inquiry can proceed to transaction\n";
    }
} else {
    echo "⚠️  No inquiries found to test\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  Test Complete!                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Next Steps:\n";
echo "1. Check the results above - all ✅ should be green\n";
echo "2. Visit an inquiry page in your browser\n";
echo "3. If property is reserved/sold/etc., you should see:\n";
echo "   - Amber warning banner at top\n";
echo "   - Disabled 'Start Transaction' button\n";
echo "4. Try changing a property status to 'reserved' and refresh\n\n";

echo "Test Properties Created/Used:\n";
echo "- Available Property ID: {$availableProperty->id}\n";
echo "- Reserved Property ID: {$reservedProperty->id}\n\n";
