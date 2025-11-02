<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== Property Images Full Test ===\n\n";

$property = Property::first();

echo "Property ID: {$property->id}\n";
echo "Title: {$property->title}\n\n";

echo "Main Image:\n";
echo "  {$property->main_image}\n\n";

echo "All Images:\n";
foreach ($property->images as $idx => $image) {
    echo "  [{$idx}] {$image}\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
