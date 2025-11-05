<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "=== Testing Required Pricing and Area Fields ===\n\n";

// Get a broker user
$broker = User::where('role', 'broker')->first();
if (!$broker) {
    echo "❌ No broker found in database\n";
    exit(1);
}

echo "Using broker: {$broker->name} (ID: {$broker->id})\n\n";

// Test 1: Try creating property WITHOUT pricing/area (should fail)
echo "Test 1: Creating property without pricing/area fields (should fail validation)...\n";
try {
    $propertyData = [
        'title' => 'Test Without Pricing - ' . now(),
        'slug' => 'test-no-pricing-' . strtoupper(substr(md5(rand()), 0, 6)),
        'description' => 'Test property without pricing',
        'type' => 'residential_lot',
        'municipality' => 'Tagbilaran City',
        'title_type' => 'titled',
        'broker_id' => $broker->id,
    ];
    
    $property = Property::create($propertyData);
    echo "❌ FAILED: Property should not have been created without pricing/area\n";
    echo "Property ID: {$property->id}\n";
    $property->forceDelete();
} catch (\Exception $e) {
    echo "✅ SUCCESS: Property creation failed as expected\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Create property WITH all required fields (should succeed)
echo "Test 2: Creating property with all required fields (should succeed)...\n";
try {
    $propertyData = [
        'title' => 'Test With Pricing - ' . now(),
        'slug' => 'test-with-pricing-' . strtoupper(substr(md5(rand()), 0, 6)),
        'description' => 'Test property with pricing',
        'type' => 'residential_lot',
        'municipality' => 'Tagbilaran City',
        'title_type' => 'titled',
        'lot_area_sqm' => 1000,
        'price_per_sqm' => 1500,
        'total_price' => 1500000,
        'broker_id' => $broker->id,
    ];
    
    $property = Property::create($propertyData);
    echo "✅ SUCCESS! Property created with ID: {$property->id}\n";
    echo "Property details:\n";
    echo "  Title: {$property->title}\n";
    echo "  Lot Area (sqm): {$property->lot_area_sqm}\n";
    echo "  Price per sqm: ₱" . number_format($property->price_per_sqm, 2) . "\n";
    echo "  Total Price: ₱" . number_format($property->total_price, 2) . "\n";
    
    // Clean up
    echo "\n🗑️ Deleting test property...\n";
    $property->forceDelete();
    echo "✅ Test property deleted.\n";
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
}

echo "\n=== All tests completed! ===\n";
