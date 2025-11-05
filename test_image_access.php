<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== Testing Image Access ===\n\n";

$property = Property::first();

if ($property) {
    echo "Property ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    echo "Main Image URL: {$property->main_image}\n";
    
    // Extract the file path from the URL
    $imagePath = str_replace('/storage/', '', $property->main_image);
    $fullPath = storage_path('app/public/' . $imagePath);
    
    echo "Full Storage Path: {$fullPath}\n";
    echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($fullPath)) {
        echo "File Size: " . formatBytes(filesize($fullPath)) . "\n";
        echo "File Type: " . mime_content_type($fullPath) . "\n";
    }
    
    // Check if public symlink works
    $publicPath = public_path('storage/' . $imagePath);
    echo "Public Path: {$publicPath}\n";
    echo "Public File Exists: " . (file_exists($publicPath) ? 'YES' : 'NO') . "\n";
} else {
    echo "No properties found in database.\n";
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}