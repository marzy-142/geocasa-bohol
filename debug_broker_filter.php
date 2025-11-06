<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "Debugging broker property type filtering...\n\n";

// Get a broker user (assuming there's at least one broker)
$broker = User::where('role', 'broker')->first();
if (!$broker) {
    echo "No broker found in database!\n";
    exit(1);
}

echo "Testing for broker: {$broker->name} (ID: {$broker->id})\n\n";

// Get all properties for this broker
$brokerProperties = Property::where('broker_id', $broker->id)->get();

echo "Broker has " . $brokerProperties->count() . " total properties:\n";
foreach ($brokerProperties as $p) {
    echo "  - ID {$p->id}: {$p->title}\n";
    echo "    Types: " . json_encode($p->types) . "\n";
    echo "    Status: {$p->status}\n\n";
}

// Test filtering by specific types
$testTypes = ['commercial_lot', 'memorial_lot'];

foreach ($testTypes as $type) {
    echo "Testing filter for type: {$type}\n";

    $query = Property::where('broker_id', $broker->id);

    $query->where(function($q) use ($type) {
        $q->orWhereJsonContains('types', $type);
    });

    $results = $query->get();

    echo "Found: " . $results->count() . " properties\n";
    foreach ($results as $p) {
        echo "  - ID {$p->id}: {$p->title}\n";
        echo "    Types: " . json_encode($p->types) . "\n";
    }
    echo "\n";
}

// Test the getBrokerPropertyTypes method
echo "Testing getBrokerPropertyTypes method:\n";
$typesMethod = collect(Property::TYPES)->map(function($type) use ($broker) {
    $count = Property::where('broker_id', $broker->id)
        ->whereJsonContains('types', $type)
        ->count();
    return [
        'value' => $type,
        'label' => Property::formatPropertyType($type),
        'count' => $count
    ];
})->filter(fn($t) => $t['count'] > 0);

echo "Available types for this broker:\n";
foreach ($typesMethod as $type) {
    echo "  - {$type['label']} ({$type['value']}): {$type['count']} properties\n";
}
