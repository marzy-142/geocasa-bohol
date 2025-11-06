<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sellerRequest = App\Models\SellerRequest::find(21);

echo "Casted uploaded_images (PHP array):\n";
$images = $sellerRequest->uploaded_images;
var_dump($images);

echo "\n\nJSON encode with default flags:\n";
echo json_encode($images) . "\n";

echo "\n\nJSON encode with JSON_UNESCAPED_SLASHES:\n";
echo json_encode($images, JSON_UNESCAPED_SLASHES) . "\n";

echo "\n\ntoArray() output for uploaded_images:\n";
$array = $sellerRequest->toArray();
var_dump($array['uploaded_images']);

echo "\n\nType of uploaded_images in toArray():\n";
echo gettype($array['uploaded_images']) . "\n";
