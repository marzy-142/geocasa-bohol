<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

echo "Testing broker property type filtering with controller logic...\n\n";

// Get a broker with commercial_lot properties
$broker = User::find(3); // Juan Dela Cruz who has commercial_lot
if (!$broker) {
    echo "Broker not found!\n";
    exit(1);
}

echo "Testing for broker: {$broker->name} (ID: {$broker->id})\n\n";

// Simulate the brokerIndex method logic
$query = Property::with([
    'broker:id,name,email',
    'client:id,name,email,phone'
])->withCount(['inquiries', 'transactions'])
->where('broker_id', $broker->id);

echo "Properties before filtering: " . $query->count() . "\n";

// Simulate filtering by types
$types = 'commercial_lot'; // Single type as sent by frontend

if ($types) {
    // Support both array and comma-separated string
    if (is_string($types)) {
        $types = explode(',', $types);
    }

    echo "Filtering by types: " . implode(', ', $types) . "\n";

    $query->where(function($q) use ($types) {
        foreach ($types as $type) {
            $type = trim($type); // Trim whitespace

            // Handle custom types (prefixed with "custom:")
            if (str_starts_with($type, 'custom:')) {
                $customType = substr($type, 7); // Remove "custom:" prefix
                $q->orWhere('custom_type_text', $customType);
                echo "Custom type filter: {$customType}\n";
            } else {
                // Handle predefined types
                $q->orWhereJsonContains('types', $type);
                echo "Standard type filter: {$type}\n";
            }
        }
    });
}

$properties = $query->latest()->get();

echo "\nFiltered results: " . $properties->count() . " properties\n";
foreach ($properties as $p) {
    echo "  - ID {$p->id}: {$p->title}\n";
    echo "    Types: " . json_encode($p->types) . "\n";
    echo "    Broker: {$p->broker->name}\n\n";
}

// Test with memorial_lot (should return 0)
echo "Testing with memorial_lot filter:\n";
$query2 = Property::where('broker_id', $broker->id);

$types2 = 'memorial_lot';
if (is_string($types2)) {
    $types2 = explode(',', $types2);
}

$query2->where(function($q) use ($types2) {
    foreach ($types2 as $type) {
        $q->orWhereJsonContains('types', $type);
    }
});

$properties2 = $query2->get();
echo "Memorial lot results: " . $properties2->count() . " properties\n";
