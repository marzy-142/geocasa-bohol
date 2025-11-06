<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sellerRequest = App\Models\SellerRequest::find(21);
echo "Raw uploaded_images from DB:\n";
print_r($sellerRequest->getRawOriginal('uploaded_images'));

echo "\n\nCasted uploaded_images:\n";
print_r($sellerRequest->uploaded_images);

echo "\n\nAs JSON (how Inertia sends it):\n";
echo json_encode($sellerRequest->uploaded_images);

echo "\n\nFull model as JSON:\n";
echo json_encode($sellerRequest->toArray());
