<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "=== Testing Minimal Property Creation ===\n\n";

// Find a broker user
$broker = User::where('role', 'broker')->where('is_approved', true)->first();

if (!$broker) {
    echo "Error: No approved broker found. Please approve a broker first.\n";
    exit(1);
}

echo "Using broker: {$broker->name} (ID: {$broker->id})\n\n";

// Test data with ONLY required fields
$minimalData = [
    'title' => 'Test Minimal Property - ' . date('Y-m-d H:i:s'),
    'slug' => 'test-minimal-' . \Illuminate\Support\Str::random(6),
    'description' => 'This is a test property with only required fields.',
    'type' => 'residential_lot',
    'municipality' => 'Tagbilaran City',
    'title_type' => 'titled',
    'broker_id' => $broker->id,
];

echo "Creating property with minimal data:\n";
echo json_encode($minimalData, JSON_PRETTY_PRINT) . "\n\n";

try {
    $property = Property::create($minimalData);
    
    echo "✅ SUCCESS! Property created with ID: {$property->id}\n\n";
    
    echo "Property details:\n";
    echo "  Title: {$property->title}\n";
    echo "  Type: {$property->type}\n";
    echo "  Municipality: {$property->municipality}\n";
    echo "  Title Type: {$property->title_type}\n";
    echo "  Barangay: " . ($property->barangay ?? 'NULL') . "\n";
    echo "  Address: " . ($property->address ?? 'NULL') . "\n";
    echo "  Lot Area (sqm): " . ($property->lot_area_sqm ?? 'NULL') . "\n";
    echo "  Price per sqm: " . ($property->price_per_sqm ?? 'NULL') . "\n";
    echo "  Total Price: " . ($property->total_price ?? 'NULL') . "\n";
    echo "  Title Number: " . ($property->title_number ?? 'NULL') . "\n";
    echo "  Zoning: " . ($property->zoning_classification ?? 'NULL') . "\n";
    
    // Clean up
    echo "\n🗑️  Deleting test property...\n";
    $property->forceDelete();
    echo "✅ Test property deleted.\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "=== All tests passed! ===\n";
