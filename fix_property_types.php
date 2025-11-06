<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing property #26 types field...\n";

// The correct value (single-encoded JSON)
$correctValue = '["other"]';

DB::table('properties')
    ->where('id', 26)
    ->update(['types' => $correctValue]);

echo "Done! Checking result...\n\n";

$property = App\Models\Property::find(26);

echo "Casted types:\n";
var_dump($property->types);

echo "\n\nformatted_types:\n";
var_dump($property->formatted_types);
