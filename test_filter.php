<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "Testing property type filtering...\n\n";

$types = 'commercial_lot';
if (is_string($types)) {
    $types = explode(',', $types);
}

echo "Searching for types: " . implode(', ', $types) . "\n\n";

$query = Property::query();
$query->where(function($q) use ($types) {
    foreach ($types as $type) {
        if (str_starts_with($type, 'custom:')) {
            $customType = substr($type, 7);
            $q->orWhere('custom_type_text', $customType);
        } else {
            $q->orWhereJsonContains('types', $type);
        }
    }
});

echo "Found: " . $query->count() . " properties\n";
echo "IDs: " . $query->pluck('id')->implode(', ') . "\n\n";

echo "Details:\n";
foreach ($query->get() as $p) {
    echo "  - ID {$p->id}: {$p->title}\n";
    echo "    Types: " . json_encode($p->types) . "\n";
    echo "    Status: {$p->status}\n\n";
}
