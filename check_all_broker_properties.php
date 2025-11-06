<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "Checking all properties and their brokers...\n\n";

$properties = Property::with('broker')->get();

echo "Total properties: " . $properties->count() . "\n\n";

$brokers = User::where('role', 'broker')->get();

foreach ($brokers as $broker) {
    echo "Broker: {$broker->name} (ID: {$broker->id})\n";
    $brokerProps = $properties->where('broker_id', $broker->id);

    echo "  Properties: " . $brokerProps->count() . "\n";

    foreach ($brokerProps as $prop) {
        echo "    - ID {$prop->id}: {$prop->title}\n";
        echo "      Types: " . json_encode($prop->types) . "\n";
        echo "      Status: {$prop->status}\n";
    }
    echo "\n";
}

// Check for commercial_lot and memorial_lot specifically
echo "Properties with commercial_lot:\n";
$commercialLots = Property::whereJsonContains('types', 'commercial_lot')->get();
foreach ($commercialLots as $prop) {
    $broker = $prop->broker;
    echo "  - ID {$prop->id}: {$prop->title} (Broker: {$broker->name})\n";
}

echo "\nProperties with memorial_lot:\n";
$memorialLots = Property::whereJsonContains('types', 'memorial_lot')->get();
foreach ($memorialLots as $prop) {
    $broker = $prop->broker;
    echo "  - ID {$prop->id}: {$prop->title} (Broker: {$broker->name})\n";
}
