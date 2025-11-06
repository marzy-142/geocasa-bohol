<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

// Test filtering for broker ID 12 (Niño Jumao-as Caturza)
$brokerId = 12;
echo "Testing broker ID $brokerId\n";
echo "Properties before filtering: " . Property::where('broker_id', $brokerId)->count() . "\n";

// Test commercial_lot filter
$query = Property::where('broker_id', $brokerId);
$query->where(function($q) {
    $q->whereJsonContains('types', 'commercial_lot')
      ->orWhere('type', 'commercial_lot');
});
$commercial = $query->get();
echo "Commercial lot results: " . $commercial->count() . " properties\n";
foreach ($commercial as $p) {
    echo "  - ID {$p->id}: {$p->title}\n";
    echo "    Types: " . json_encode($p->types) . "\n";
}

// Test residential_lot filter
$query2 = Property::where('broker_id', $brokerId);
$query2->where(function($q) {
    $q->whereJsonContains('types', 'residential_lot')
      ->orWhere('type', 'residential_lot');
});
$residential = $query2->get();
echo "Residential lot results: " . $residential->count() . " properties\n";
foreach ($residential as $p) {
    echo "  - ID {$p->id}: {$p->title}\n";
    echo "    Types: " . json_encode($p->types) . "\n";
}
