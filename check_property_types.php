<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING PROPERTY TYPES DATA ===\n\n";

$properties = DB::table('properties')
    ->select('id', 'title', 'type', 'types')
    ->get();

foreach ($properties as $property) {
    echo "ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    echo "Old 'type' field: " . ($property->type ?? 'NULL') . "\n";
    echo "New 'types' field: " . ($property->types ?? 'NULL') . "\n";
    
    // Try to decode JSON
    if ($property->types) {
        $decoded = json_decode($property->types, true);
        if (is_array($decoded)) {
            echo "Decoded types: " . implode(', ', $decoded) . "\n";
        } else {
            echo "Types is not valid JSON!\n";
        }
    }
    echo "---\n";
}

echo "\nTotal properties: " . $properties->count() . "\n";
