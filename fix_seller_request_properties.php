<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\SellerRequest;
use Illuminate\Support\Facades\DB;

echo "=== FIXING PROPERTIES CREATED FROM SELLER REQUESTS ===\n\n";

// Find all seller requests that have been converted to properties
$sellerRequests = SellerRequest::whereNotNull('property_id')->get();

echo "Found {$sellerRequests->count()} properties created from seller requests\n\n";

$fixed = 0;
$skipped = 0;

foreach ($sellerRequests as $sellerRequest) {
    $property = Property::find($sellerRequest->property_id);
    
    if (!$property) {
        echo "Property ID {$sellerRequest->property_id} not found, skipping...\n\n";
        continue;
    }
    
    echo "Property ID {$property->id}: {$property->title}\n";
    
    $updates = [];
    
    // Fix municipality (use municipality or fall back to city)
    if (empty($property->municipality)) {
        $municipality = $sellerRequest->municipality ?? $sellerRequest->city;
        if ($municipality) {
            $updates['municipality'] = $municipality;
            echo "  ✓ Setting municipality: {$municipality}\n";
        }
    }
    
    // Fix barangay
    if (empty($property->barangay) && !empty($sellerRequest->barangay)) {
        $updates['barangay'] = $sellerRequest->barangay;
        echo "  ✓ Setting barangay: {$sellerRequest->barangay}\n";
    }
    
    // Fix address
    if (empty($property->address) && !empty($sellerRequest->address)) {
        $updates['address'] = $sellerRequest->address;
        echo "  ✓ Setting address: {$sellerRequest->address}\n";
    }
    
    // Fix lot_area_sqm if it's 0 but seller request has lot_area
    if ($property->lot_area_sqm == 0 && $sellerRequest->lot_area > 0) {
        $updates['lot_area_sqm'] = $sellerRequest->lot_area;
        $updates['lot_area_hectares'] = round($sellerRequest->lot_area / 10000, 4);
        echo "  ✓ Setting lot_area_sqm: {$sellerRequest->lot_area}\n";
        echo "  ✓ Setting lot_area_hectares: " . round($sellerRequest->lot_area / 10000, 4) . "\n";
    }
    
    // Recalculate price_per_sqm if we have lot_area_sqm and total_price
    if (isset($updates['lot_area_sqm']) && $updates['lot_area_sqm'] > 0 && $property->total_price > 0) {
        $pricePerSqm = round($property->total_price / $updates['lot_area_sqm'], 2);
        $updates['price_per_sqm'] = $pricePerSqm;
        echo "  ✓ Recalculating price_per_sqm: {$pricePerSqm}\n";
    }
    
    // Apply updates
    if (!empty($updates)) {
        $property->update($updates);
        $fixed++;
        echo "  ✅ Property updated!\n\n";
    } else {
        $skipped++;
        echo "  ⏭️  No updates needed\n\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total properties: {$sellerRequests->count()}\n";
echo "Fixed: {$fixed}\n";
echo "Skipped: {$skipped}\n";
echo "\nDone!\n";
