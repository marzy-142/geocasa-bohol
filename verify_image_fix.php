<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== PROPERTY IMAGE FIX VERIFICATION ===\n\n";

// Test multiple properties
$properties = Property::limit(10)->get();

echo "Testing " . count($properties) . " properties...\n\n";

$successCount = 0;
$failCount = 0;

foreach ($properties as $property) {
    echo "Property #{$property->id}: {$property->title}\n";
    
    // Check main_image
    $mainImage = $property->main_image;
    $isValidMainImage = (str_starts_with($mainImage, 'http://') || str_starts_with($mainImage, 'https://') || str_starts_with($mainImage, '/storage/'));
    
    echo "  Main Image: " . substr($mainImage, 0, 80) . "...\n";
    echo "  Valid? " . ($isValidMainImage ? "✓ YES" : "✗ NO") . "\n";
    
    // Check images array
    $images = $property->images;
    $validImagesCount = 0;
    if (is_array($images)) {
        foreach ($images as $img) {
            if ($img && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/storage/'))) {
                $validImagesCount++;
            }
        }
    }
    
    echo "  Images Array: " . count($images) . " total, {$validImagesCount} valid\n";
    
    if ($isValidMainImage && $validImagesCount == count($images)) {
        echo "  Status: ✓ PASS\n";
        $successCount++;
    } else {
        echo "  Status: ✗ FAIL\n";
        $failCount++;
    }
    
    echo "\n";
}

echo str_repeat('=', 80) . "\n";
echo "SUMMARY:\n";
echo "  Passed: {$successCount}\n";
echo "  Failed: {$failCount}\n";
echo "  Total:  " . count($properties) . "\n";
echo "\n";

if ($failCount === 0) {
    echo "✓ ALL TESTS PASSED! Property images are displaying correctly.\n";
} else {
    echo "✗ Some tests failed. Please review the output above.\n";
}

echo str_repeat('=', 80) . "\n";
