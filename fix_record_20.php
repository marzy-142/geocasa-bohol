<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SellerRequest;

echo "Fixing record 20...\n";

$sr = SellerRequest::find(20);

if ($sr) {
    // Set the images array directly
    $sr->uploaded_images = ['seller-requests/images/1762355611_sQwhZD0W.jpg'];
    $sr->save();
    
    echo "✓ Fixed!\n";
    echo "New value: " . json_encode($sr->uploaded_images) . "\n";
} else {
    echo "✗ Record not found\n";
}
