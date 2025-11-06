<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SellerRequest;

$sellerRequest = SellerRequest::latest()->first();

if ($sellerRequest) {
    echo "=== Latest Seller Request Images ===\n";
    echo "ID: " . $sellerRequest->id . "\n";
    echo "Name: " . $sellerRequest->name . "\n";
    echo "Property Title: " . $sellerRequest->property_title . "\n\n";
    
    echo "Uploaded Images (raw):\n";
    echo $sellerRequest->getRawOriginal('uploaded_images') . "\n\n";
    
    echo "Uploaded Images (casted):\n";
    print_r($sellerRequest->uploaded_images);
    echo "\n";
    
    if (is_array($sellerRequest->uploaded_images)) {
        echo "Count: " . count($sellerRequest->uploaded_images) . " images\n";
        foreach ($sellerRequest->uploaded_images as $index => $image) {
            echo "Image " . ($index + 1) . ": " . $image . "\n";
        }
    }
} else {
    echo "No seller requests found.\n";
}
