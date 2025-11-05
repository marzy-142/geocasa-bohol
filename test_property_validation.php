<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Property;
use App\Http\Requests\PropertyFileUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Property Creation Validation Test\n";
echo "================================\n\n";

// Test broker
$broker = User::where('email', 'maria@geocasabohol.com')->first();

if (!$broker) {
    echo "❌ Broker not found\n";
    exit;
}

echo "✓ Testing with broker: {$broker->name}\n";
echo "✓ Broker is approved: " . ($broker->is_approved ? 'Yes' : 'No') . "\n\n";

// Test data - minimal property
$testData = [
    'title' => 'Test Property Creation',
    'type' => 'residential_lot',
    'description' => 'A test property to verify validation',
    'municipality' => 'Tagbilaran City',
    'title_type' => 'titled',
    'lot_area_sqm' => 100,
    'price_per_sqm' => 5000,
    'total_price' => 500000,
    'status' => 'available',
    // Boolean fields
    'road_access' => true,
    'electricity_available' => true,
    'water_source' => true,
    'internet_available' => false,
    'is_featured' => false,
    'has_virtual_tour' => false,
];

echo "Test Data:\n";
foreach ($testData as $key => $value) {
    echo "  {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
}
echo "\n";

// Create validator using the same rules as PropertyFileUploadRequest
$rules = [
    // Property basic info - REQUIRED FIELDS
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'type' => 'required|in:' . implode(',', \App\Models\Property::TYPES),
    'municipality' => 'required|in:' . implode(',', \App\Models\Property::BOHOL_MUNICIPALITIES),
    'title_type' => 'required|in:titled,tax_declared,mother_title,cct',
    
    // Pricing and area - REQUIRED
    'lot_area_sqm' => 'required|numeric|min:0',
    'price_per_sqm' => 'required|numeric|min:0',
    'total_price' => 'required|numeric|min:0',
    
    // Optional fields
    'status' => 'nullable|in:' . implode(',', \App\Models\Property::STATUSES),
    
    // Amenities (boolean fields)
    'road_access' => 'boolean',
    'electricity_available' => 'boolean',
    'water_source' => 'boolean',
    'internet_available' => 'boolean',
    'is_featured' => 'boolean',
    'has_virtual_tour' => 'boolean',
];

$validator = Validator::make($testData, $rules);

echo "Validation Results:\n";
echo "==================\n";

if ($validator->fails()) {
    echo "❌ Validation FAILED\n\n";
    echo "Errors:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - {$error}\n";
    }
} else {
    echo "✅ Validation PASSED\n\n";
    
    // Try to create the property
    try {
        $propertyData = $testData;
        $propertyData['broker_id'] = $broker->id;
        $propertyData['slug'] = \Illuminate\Support\Str::slug($propertyData['title']) . '-' . \Illuminate\Support\Str::random(6);
        
        // Calculate hectares
        if (!empty($propertyData['lot_area_sqm'])) {
            $propertyData['lot_area_hectares'] = $propertyData['lot_area_sqm'] / 10000;
        }
        
        $property = Property::create($propertyData);
        echo "✅ Property created successfully!\n";
        echo "   ID: {$property->id}\n";
        echo "   Title: {$property->title}\n";
        echo "   Slug: {$property->slug}\n";
        
        // Clean up
        $property->delete();
        echo "✅ Test property deleted (cleanup)\n";
        
    } catch (\Exception $e) {
        echo "❌ Property creation failed: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\nTest completed.\n";