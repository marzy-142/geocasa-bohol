<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use Illuminate\Support\Facades\Storage;

echo "=== Storage Path Debugging ===\n\n";

$property = Property::first();

echo "Property ID: {$property->id}\n";
echo "Title: {$property->title}\n";
echo "Raw images: " . json_encode($property->images) . "\n\n";

$images = $property->images;
if (is_array($images) && !empty($images)) {
    $imagePath = $images[0];
    echo "First image (raw): {$imagePath}\n";
    
    $cleanPath = $imagePath;
    if (str_starts_with($cleanPath, '/storage/')) {
        $cleanPath = substr($cleanPath, strlen('/storage/'));
    }
    if (str_starts_with($cleanPath, 'public/')) {
        $cleanPath = substr($cleanPath, strlen('public/'));
    }
    $cleanPath = ltrim($cleanPath, '/');
    
    echo "Cleaned path: {$cleanPath}\n";
    echo "Checking if exists with: properties/images/{$cleanPath}\n";
    echo "Exists? " . (Storage::disk('public')->exists("properties/images/{$cleanPath}") ? 'YES' : 'NO') . "\n";
    echo "Direct check: " . (Storage::disk('public')->exists($cleanPath) ? 'YES' : 'NO') . "\n";
    
    // List what's in the properties/images directory
    echo "\nFiles in properties/images:\n";
    $files = Storage::disk('public')->files('properties/images');
    foreach (array_slice($files, 0, 10) as $file) {
        echo "  - {$file}\n";
    }
}
