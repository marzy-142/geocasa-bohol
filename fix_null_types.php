<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing NULL types fields by migrating from old 'type' field...\n";

$properties = DB::table('properties')
    ->whereNull('types')
    ->whereNotNull('type')
    ->get();

echo "Found " . $properties->count() . " properties with NULL types but valid old type.\n";

foreach ($properties as $property) {
    $typesJson = json_encode([$property->type]);

    DB::table('properties')
        ->where('id', $property->id)
        ->update(['types' => $typesJson]);

    echo "Fixed property ID {$property->id}: {$property->type} -> {$typesJson}\n";
}

echo "Done!\n";
