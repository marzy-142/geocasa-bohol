<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

// Get property by slug from the URL shown in the screenshot
$slug = 'dkjfnsafnjsdf-1761904660';
$property = Property::where('slug', $slug)->first();

if (!$property) {
    echo "Property not found with slug: $slug\n";
    echo "Trying to find any recent properties...\n";
    $property = Property::orderBy('created_at', 'desc')->first();
}

if ($property) {
    echo "=== PROPERTY DATA ===\n";
    echo "ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    echo "Slug: {$property->slug}\n";
    echo "Type: {$property->type}\n";
    echo "Municipality: " . ($property->municipality ?? 'NULL') . "\n";
    echo "Barangay: " . ($property->barangay ?? 'NULL') . "\n";
    echo "Address: " . ($property->address ?? 'NULL') . "\n";
    echo "Lot Area SQM: {$property->lot_area_sqm}\n";
    echo "Lot Area Hectares: {$property->lot_area_hectares}\n";
    echo "Total Price: {$property->total_price}\n";
    echo "Price per SQM: {$property->price_per_sqm}\n";
    echo "Images: " . json_encode($property->images) . "\n";
    echo "Broker ID: {$property->broker_id}\n";
    echo "\n=== RAW ATTRIBUTES ===\n";
    print_r($property->getAttributes());
} else {
    echo "No properties found in database\n";
}
