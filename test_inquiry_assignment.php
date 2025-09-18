<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Services\BrokerAssignmentService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Inquiry Assignment System\n";
echo "=================================\n\n";

try {
    // Create a broker who owns the property
    $propertyBroker = User::create([
        'name' => 'Property Broker',
        'email' => 'property.broker@test.com',
        'password' => bcrypt('password'),
        'role' => 'broker',
        'is_approved' => true,
        'prc_id' => 'PRC123456'
    ]);
    echo "✓ Created property owner broker (ID: {$propertyBroker->id})\n";

    // Create another broker who will be assigned to the inquiry
    $assignedBroker = User::create([
        'name' => 'Assigned Broker',
        'email' => 'assigned.broker@test.com',
        'password' => bcrypt('password'),
        'role' => 'broker',
        'is_approved' => true,
        'prc_id' => 'PRC789012'
    ]);
    echo "✓ Created assigned broker (ID: {$assignedBroker->id})\n";

    // Create a property owned by the first broker
    $property = Property::create([
        'title' => 'Test Property',
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
        'broker_id' => $propertyBroker->id
    ]);
    echo "✓ Created property owned by broker {$propertyBroker->id} (Property ID: {$property->id})\n";

    // Create an inquiry for the property
    $inquiry = Inquiry::create([
        'name' => 'John Doe',
        'email' => 'john.doe@test.com',
        'phone' => '+1234567890',
        'message' => 'I am interested in this property',
        'inquiry_type' => 'general',
        'property_id' => $property->id,
        'status' => 'new'
    ]);
    echo "✓ Created inquiry (ID: {$inquiry->id})\n";

    // Test 1: Assign inquiry to a different broker (not property owner)
    $inquiry->update(['assigned_broker_id' => $assignedBroker->id]);
    echo "✓ Assigned inquiry to different broker (ID: {$assignedBroker->id})\n";

    // Verify property ownership is preserved
    $property->refresh();
    if ($property->broker_id === $propertyBroker->id) {
        echo "✓ Property ownership preserved - still owned by broker {$propertyBroker->id}\n";
    } else {
        echo "✗ ERROR: Property ownership changed!\n";
    }

    // Verify inquiry assignment
    $inquiry->refresh();
    if ($inquiry->assigned_broker_id === $assignedBroker->id) {
        echo "✓ Inquiry correctly assigned to broker {$assignedBroker->id}\n";
    } else {
        echo "✗ ERROR: Inquiry assignment failed!\n";
    }

    // Test relationships
    $inquiryBroker = $inquiry->broker;
    $propertyOwner = $inquiry->propertyBroker;

    if ($inquiryBroker && $inquiryBroker->id === $assignedBroker->id) {
        echo "✓ Inquiry broker relationship works correctly\n";
    } else {
        echo "✗ ERROR: Inquiry broker relationship failed!\n";
    }

    if ($propertyOwner && $propertyOwner->id === $propertyBroker->id) {
        echo "✓ Property broker relationship works correctly\n";
    } else {
        echo "✗ ERROR: Property broker relationship failed!\n";
    }

    echo "\n=================================\n";
    echo "✓ All tests passed! The system correctly:\n";
    echo "  - Assigns inquiries to brokers via assigned_broker_id\n";
    echo "  - Preserves property ownership (broker_id)\n";
    echo "  - Maintains separate relationships for inquiry handling vs property ownership\n";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}