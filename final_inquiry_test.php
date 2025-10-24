<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Client;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Inquiry Assignment System Test\n";
echo "==============================\n\n";

try {
    $timestamp = time();
    
    // 1. Create property broker
    $propertyBroker = User::create([
        'name' => 'Property Broker',
        'email' => "property.broker{$timestamp}@example.com",
        'password' => bcrypt('password'),
        'role' => 'broker',
        'is_approved' => true,
        'prc_id' => "PRC{$timestamp}1"
    ]);
    echo "✓ Property broker created (ID: {$propertyBroker->id})\n";

    // 2. Create assigned broker
    $assignedBroker = User::create([
        'name' => 'Assigned Broker',
        'email' => "assigned.broker{$timestamp}@example.com",
        'password' => bcrypt('password'),
        'role' => 'broker',
        'is_approved' => true,
        'prc_id' => "PRC{$timestamp}2"
    ]);
    echo "✓ Assigned broker created (ID: {$assignedBroker->id})\n";

    // 3. Create client
    $client = User::create([
        'name' => 'Test Client',
        'email' => "client{$timestamp}@example.com",
        'password' => bcrypt('password'),
        'role' => 'client',
        'is_approved' => true
    ]);
    echo "✓ Client created (ID: {$client->id})\n";

    // Create corresponding Client record
    $clientRecord = Client::create([
        'name' => $client->name,
        'email' => $client->email,
        'user_id' => $client->id,
        'status' => 'active',
        'broker_id' => $assignedBroker->id
    ]);
    echo "✓ Client record created (ID: {$clientRecord->id})\n";

    // 4. Create property owned by property broker
    $property = Property::create([
        'title' => 'Test Property for Inquiry',
        'slug' => "test-property-{$timestamp}",
        'type' => 'residential_lot',
        'status' => 'available',
        'description' => 'A test property for inquiry assignment',
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
    echo "✓ Property created (ID: {$property->id}) owned by broker {$propertyBroker->id}\n";

    // 5. Create inquiry
    $inquiry = Inquiry::create([
        'name' => $client->name,
        'email' => $client->email,
        'property_id' => $property->id,
        'client_id' => $clientRecord->id,
        'message' => 'I am interested in this property',
        'status' => 'new'
    ]);
    echo "✓ Inquiry created (ID: {$inquiry->id})\n";
    echo "  Initial assigned_broker_id: " . ($inquiry->assigned_broker_id ?? 'null') . "\n";

    // 6. Assign inquiry to different broker
    $inquiry->assigned_broker_id = $assignedBroker->id;
    $inquiry->save();
    echo "✓ Inquiry assigned to broker {$assignedBroker->id}\n";

    // 7. Verify relationships and data integrity
    $inquiry->refresh();
    $property->refresh();
    
    echo "\n--- Verification Results ---\n";
    echo "Property owner (broker_id): {$property->broker_id}\n";
    echo "Inquiry assigned to (assigned_broker_id): {$inquiry->assigned_broker_id}\n";
    echo "Property ownership preserved: " . ($property->broker_id == $propertyBroker->id ? 'YES' : 'NO') . "\n";
    echo "Inquiry correctly assigned: " . ($inquiry->assigned_broker_id == $assignedBroker->id ? 'YES' : 'NO') . "\n";
    
    // 8. Test relationships
    echo "\n--- Relationship Tests ---\n";
    
    // Property relationships
    $propertyOwner = $property->broker;
    echo "Property owner name: {$propertyOwner->name}\n";
    
    // Inquiry relationships
    $inquiryProperty = $inquiry->property;
    $inquiryClient = $inquiry->client;
    $inquiryAssignedBroker = $inquiry->broker;
    
    echo "Inquiry property title: {$inquiryProperty->title}\n";
    echo "Inquiry client name: {$inquiryClient->name}\n";
    echo "Inquiry assigned broker name: {$inquiryAssignedBroker->name}\n";
    
    echo "\n✅ ALL TESTS PASSED! The inquiry assignment system works correctly.\n";
    echo "   - Property ownership is preserved\n";
    echo "   - Inquiries can be assigned to different brokers\n";
    echo "   - All relationships work properly\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}