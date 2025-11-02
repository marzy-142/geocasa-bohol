<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inquiry;

echo "=== UI COMPONENT DATA VERIFICATION ===\n\n";

$inquiry = Inquiry::with([
    'property:id,title,slug,address,municipality,type,total_price,broker_id,images',
    'property.broker:id,name,email',
    'client:id,name,email,phone',
    'transaction:id,inquiry_id,status,transaction_number,created_at,updated_at,inquiry_date,first_contact_date,viewing_date,offer_date,acceptance_date,contract_date,closing_date,finalized_date,offered_price,final_price',
    'conversation:id,type,last_message_at'
])->find(36); // Using inquiry #36 from our test results

if (!$inquiry) {
    echo "Inquiry not found!\n";
    exit(1);
}

echo "Testing with Inquiry #{$inquiry->id}\n\n";

// Test 1: Basic Inquiry Data
echo "=== 1. INQUIRY DATA (for DealProgressCard) ===\n";
echo "✓ ID: {$inquiry->id}\n";
echo "✓ Status: {$inquiry->status}\n";
echo "✓ Created: {$inquiry->created_at}\n";
echo "✓ Contacted: " . ($inquiry->contacted_at ?? 'NULL') . "\n";
echo "✓ Scheduled: " . ($inquiry->scheduled_at ?? 'NULL') . "\n";
echo "✓ Responded: " . ($inquiry->responded_at ?? 'NULL') . "\n";
echo "✓ Completion Outcome: " . ($inquiry->completion_outcome ?? 'NULL') . "\n";

// Test 2: Property Data
echo "\n=== 2. PROPERTY DATA (for Timeline) ===\n";
if ($inquiry->property) {
    echo "✓ Title: {$inquiry->property->title}\n";
    echo "✓ Type: " . ($inquiry->property->type ?? 'NULL') . "\n";
    echo "✓ Municipality: " . ($inquiry->property->municipality ?? 'NULL') . "\n";
    echo "✓ Price: ₱" . number_format($inquiry->property->total_price ?? 0) . "\n";
} else {
    echo "✗ Property not loaded!\n";
}

// Test 3: Client Data
echo "\n=== 3. CLIENT DATA (for Timeline) ===\n";
if ($inquiry->client) {
    echo "✓ Name: {$inquiry->client->name}\n";
    echo "✓ Email: {$inquiry->client->email}\n";
    echo "✓ Phone: " . ($inquiry->client->phone ?? 'NULL') . "\n";
} else {
    echo "✗ Client not loaded!\n";
}

// Test 4: Transaction Data (CRITICAL for both components)
echo "\n=== 4. TRANSACTION DATA (for both components) ===\n";
if ($inquiry->transaction) {
    echo "✓ Transaction Number: {$inquiry->transaction->transaction_number}\n";
    echo "✓ Status: {$inquiry->transaction->status}\n";
    echo "✓ Created: {$inquiry->transaction->created_at}\n";
    echo "✓ Updated: {$inquiry->transaction->updated_at}\n";
    echo "\nTimeline Dates:\n";
    echo "  - Inquiry Date: " . ($inquiry->transaction->inquiry_date ?? 'NULL') . "\n";
    echo "  - First Contact: " . ($inquiry->transaction->first_contact_date ?? 'NULL') . "\n";
    echo "  - Viewing Date: " . ($inquiry->transaction->viewing_date ?? 'NULL') . "\n";
    echo "  - Offer Date: " . ($inquiry->transaction->offer_date ?? 'NULL') . "\n";
    echo "  - Acceptance Date: " . ($inquiry->transaction->acceptance_date ?? 'NULL') . "\n";
    echo "  - Contract Date: " . ($inquiry->transaction->contract_date ?? 'NULL') . "\n";
    echo "  - Closing Date: " . ($inquiry->transaction->closing_date ?? 'NULL') . "\n";
    echo "  - Finalized Date: " . ($inquiry->transaction->finalized_date ?? 'NULL') . "\n";
    echo "\nPricing:\n";
    echo "  - Offered Price: ₱" . number_format((float)$inquiry->transaction->offered_price ?? 0) . "\n";
    echo "  - Final Price: " . ($inquiry->transaction->final_price ? '₱' . number_format((float)$inquiry->transaction->final_price) : 'NULL') . "\n";
} else {
    echo "✗ Transaction not loaded!\n";
}

// Test 5: Calculate Expected Progress
echo "\n=== 5. PROGRESS CALCULATION TEST ===\n";
$stages = [
    ['name' => 'Inquiry Received', 'completed' => true],
    ['name' => 'Initial Contact', 'completed' => (bool)$inquiry->contacted_at],
    ['name' => 'Viewing Scheduled', 'completed' => (bool)$inquiry->scheduled_at],
    ['name' => 'Offer Made', 'completed' => (bool)$inquiry->transaction],
    ['name' => 'Negotiation', 'completed' => $inquiry->transaction && in_array($inquiry->transaction->status, ['negotiation', 'offer_accepted', 'contract_signed', 'finalized'])],
    ['name' => 'Contract Signed', 'completed' => $inquiry->transaction && in_array($inquiry->transaction->status, ['contract_signed', 'finalized'])],
    ['name' => 'Finalized', 'completed' => $inquiry->transaction && $inquiry->transaction->status === 'finalized'],
];

$completed = array_filter($stages, fn($s) => $s['completed']);
$percentage = round((count($completed) / count($stages)) * 100);

echo "Completed Stages: " . count($completed) . "/" . count($stages) . "\n";
echo "Expected Progress: {$percentage}%\n";
echo "\nStage Breakdown:\n";
foreach ($stages as $stage) {
    $icon = $stage['completed'] ? '✓' : '✗';
    echo "  {$icon} {$stage['name']}\n";
}

// Test 6: Timeline Events Count
echo "\n=== 6. TIMELINE EVENTS COUNT ===\n";
$eventCount = 1; // Inquiry received (always)
if ($inquiry->contacted_at) $eventCount++;
if ($inquiry->responded_at) $eventCount++;
if ($inquiry->scheduled_at) $eventCount++;
if ($inquiry->completion_outcome === 'won') $eventCount++;
if ($inquiry->transaction) {
    $eventCount++; // Transaction created
    if ($inquiry->transaction->offer_date) $eventCount++;
    if ($inquiry->transaction->acceptance_date) $eventCount++;
    if ($inquiry->transaction->contract_date) $eventCount++;
    if ($inquiry->transaction->status === 'finalized' && $inquiry->transaction->finalized_date) $eventCount++;
}

echo "Expected Timeline Events: {$eventCount}\n";

// Test 7: Next Action Prediction
echo "\n=== 7. NEXT ACTION PREDICTION ===\n";
$currentStageIndex = -1;
foreach ($stages as $index => $stage) {
    if (!$stage['completed']) {
        $currentStageIndex = $index;
        break;
    }
}

if ($currentStageIndex !== -1) {
    $currentStage = $stages[$currentStageIndex];
    echo "Current Stage: {$currentStage['name']}\n";
    
    $actions = [
        'Initial Contact' => 'Reach out to the client to discuss their interest and answer questions.',
        'Viewing Scheduled' => 'Schedule a property viewing appointment with the client.',
        'Offer Made' => 'Mark the inquiry as won to create a transaction and move to the offer stage.',
        'Negotiation' => 'Work with the client on price negotiations and terms.',
        'Contract Signed' => 'Prepare and execute the purchase agreement.',
        'Finalized' => 'Complete the final paperwork and close the deal.'
    ];
    
    $action = $actions[$currentStage['name']] ?? 'Continue with the next step';
    echo "Expected Next Action: {$action}\n";
} else {
    echo "All stages completed!\n";
}

echo "\n=== ✓ DATA VERIFICATION COMPLETE ===\n";
echo "\nThe components should display:\n";
echo "- DealProgressCard: {$percentage}% progress with " . count($completed) . " stages completed\n";
echo "- UnifiedTimeline: {$eventCount} events in chronological order\n";
echo "\nTest URL: http://localhost:8000/inquiries/{$inquiry->id}\n";
