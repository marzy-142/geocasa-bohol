<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== Property Images Debug ===\n\n";

$properties = Property::limit(5)->get();

foreach ($properties as $property) {
    echo "Property ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    echo "Type: {$property->type}\n";
    echo "Images (raw JSON): " . json_encode($property->images) . "\n";
    echo "Main Image Attribute: {$property->main_image}\n";
    echo "Is SVG placeholder: " . (str_contains($property->main_image, 'data:image/svg') ? 'YES' : 'NO') . "\n";
    echo "\n" . str_repeat('-', 80) . "\n\n";
}
