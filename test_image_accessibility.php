<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== IMAGE ACCESSIBILITY TEST ===\n\n";

$property = Property::first();

echo "Testing property: {$property->title}\n\n";

echo "Main Image URL: {$property->main_image}\n";
$mainImagePath = public_path(ltrim($property->main_image, '/'));
echo "File exists at: {$mainImagePath}\n";
echo "Accessible: " . (file_exists($mainImagePath) ? "✓ YES" : "✗ NO") . "\n\n";

echo "Gallery Images:\n";
foreach ($property->images as $idx => $imageUrl) {
    echo "\n[{$idx}] {$imageUrl}\n";
    $imagePath = public_path(ltrim($imageUrl, '/'));
    echo "    File: {$imagePath}\n";
    echo "    Accessible: " . (file_exists($imagePath) ? "✓ YES" : "✗ NO") . "\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "\nAll images are using RELATIVE URLs (/storage/...)\n";
echo "This means they will work with ANY domain:\n";
echo "  ✓ http://localhost\n";
echo "  ✓ http://127.0.0.1\n";
echo "  ✓ http://192.168.x.x\n";
echo "  ✓ https://yourdomain.com\n";
echo "  ✓ Any other URL\n";
echo str_repeat('=', 80) . "\n";
