<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use Illuminate\Support\Facades\Storage;

echo "=== DETAILED IMAGE DEBUG ===\n\n";

$property = Property::first();

echo "Property: {$property->title}\n\n";

// Check raw database value
echo "RAW images column from database:\n";
echo "  " . $property->getAttributes()['images'] . "\n\n";

// Check main_image attribute
echo "main_image attribute:\n";
echo "  " . $property->main_image . "\n\n";

// Check images attribute
echo "images attribute:\n";
print_r($property->images);

// Check if files actually exist
echo "\nFile existence checks:\n";
$rawImages = json_decode($property->getAttributes()['images'], true);
foreach ($rawImages as $img) {
    $paths = [
        "properties/images/{$img}",
        $img,
        "public/properties/images/{$img}",
    ];
    
    echo "\nChecking for: {$img}\n";
    foreach ($paths as $path) {
        $exists = Storage::disk('public')->exists($path);
        echo "  Storage::disk('public')->exists('{$path}'): " . ($exists ? 'YES' : 'NO') . "\n";
    }
    
    // Check filesystem directly
    $fullPath = storage_path("app/public/properties/images/{$img}");
    echo "  file_exists('{$fullPath}'): " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    
    // Check via symlink
    $symlinkPath = public_path("storage/properties/images/{$img}");
    echo "  Symlink path exists: " . (file_exists($symlinkPath) ? 'YES' : 'NO') . "\n";
}
