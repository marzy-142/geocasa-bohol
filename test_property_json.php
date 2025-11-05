<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== Property JSON Test ===\n\n";

$property = Property::first();

if ($property) {
    $propertyArray = $property->toArray();
    echo "Property ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    echo "Raw Images: " . json_encode($propertyArray['images']) . "\n";
    echo "Main Image: {$propertyArray['main_image']}\n";
    echo "Images Array: " . json_encode($propertyArray['images']) . "\n";
} else {
    echo "No properties found.\n";
}