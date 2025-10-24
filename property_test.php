<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Property;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Property Creation Test\n";
echo "=====================\n\n";

try {
    // Create a broker first
    $broker = User::create([
        'name' => 'Test Broker',
        'email' => 'broker' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'broker',
        'is_approved' => true,
        'prc_id' => 'PRC' . time()
    ]);
    echo "✓ Broker created (ID: {$broker->id})\n";
    
    // Create a property
    $property = Property::create([
        'title' => 'Test Property',
        'slug' => 'test-property-' . time(),
        'type' => 'residential_lot',
        'status' => 'available',
        'description' => 'A test property',
        'address' => '123 Test Street',
        'municipality' => 'Tagbilaran City',
        'barangay' => 'Poblacion',
        'lot_area_sqm' => 100,
        'price_per_sqm' => 5000,
        'total_price' => 500000,
        'road_access' => true,
        'water_source' => true,
        'electricity_available' => true,
        'internet_available' => true,
        'broker_id' => $broker->id
    ]);
    
    echo "✓ Property created successfully (ID: {$property->id})\n";
    echo "  Title: {$property->title}\n";
    echo "  Type: {$property->type}\n";
    echo "  Broker ID: {$property->broker_id}\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}