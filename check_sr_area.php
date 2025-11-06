<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sr = App\Models\SellerRequest::find(21);

echo "Seller Request #21 Area Fields:\n";
echo "property_area: " . ($sr->property_area ?? 'NULL') . "\n";
echo "area_unit: " . ($sr->area_unit ?? 'NULL') . "\n";
echo "lot_area: " . ($sr->lot_area ?? 'NULL') . "\n";
echo "asking_price: " . ($sr->asking_price ?? 'NULL') . "\n";
