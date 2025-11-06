<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$property = App\Models\Property::find(26);

if ($property) {
    echo "Fixing property #26...\n";
    
    // Calculate correct values
    $lotAreaSqm = 120;
    $totalPrice = 5000000;
    $pricePerSqm = round($totalPrice / $lotAreaSqm, 2);
    $lotAreaHectares = round($lotAreaSqm / 10000, 4);
    
    echo "Setting lot_area_sqm: $lotAreaSqm\n";
    echo "Setting lot_area_hectares: $lotAreaHectares\n";
    echo "Setting price_per_sqm: $pricePerSqm\n";
    
    $property->update([
        'lot_area_sqm' => $lotAreaSqm,
        'lot_area_hectares' => $lotAreaHectares,
        'price_per_sqm' => $pricePerSqm,
    ]);
    
    echo "\nDone! Property updated.\n";
    echo "Price per sqm: ₱" . number_format($property->price_per_sqm, 2) . "\n";
    echo "Lot area: " . $property->lot_area_sqm . " sqm\n";
} else {
    echo "Property #26 not found.\n";
}
