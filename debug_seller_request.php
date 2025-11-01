<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SellerRequest;
use App\Models\Property;

// Get the property
$property = Property::find(10);

if ($property) {
    echo "=== PROPERTY ===\n";
    echo "ID: {$property->id}\n";
    echo "Title: {$property->title}\n";
    
    // Find the seller request
    $sellerRequest = SellerRequest::where('property_id', $property->id)->first();
    
    if ($sellerRequest) {
        echo "\n=== SELLER REQUEST ===\n";
        echo "ID: {$sellerRequest->id}\n";
        echo "Name: {$sellerRequest->name}\n";
        echo "Email: {$sellerRequest->email}\n";
        echo "Phone: {$sellerRequest->phone}\n";
        echo "Address: {$sellerRequest->address}\n";
        echo "City: " . ($sellerRequest->city ?? 'NULL') . "\n";
        echo "Municipality: " . ($sellerRequest->municipality ?? 'NULL') . "\n";
        echo "Barangay: " . ($sellerRequest->barangay ?? 'NULL') . "\n";
        echo "Province: " . ($sellerRequest->province ?? 'NULL') . "\n";
        echo "Postal Code: " . ($sellerRequest->postal_code ?? 'NULL') . "\n";
        echo "Property Title: {$sellerRequest->property_title}\n";
        echo "Property Type: {$sellerRequest->property_type}\n";
        echo "Lot Area: {$sellerRequest->lot_area}\n";
        echo "Asking Price: {$sellerRequest->asking_price}\n";
        echo "Features: " . json_encode($sellerRequest->features) . "\n";
        echo "Images: " . json_encode($sellerRequest->uploaded_images) . "\n";
        echo "\n=== RAW SELLER REQUEST ATTRIBUTES ===\n";
        print_r($sellerRequest->getAttributes());
    } else {
        echo "\nNo seller request found for this property\n";
    }
} else {
    echo "Property not found\n";
}
