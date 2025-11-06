<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// The correct value (single-encoded JSON)
$correctValue = '["seller-requests/images/1762356468_2CjLqMYC.jpg"]';

echo "Fixing seller_request #21...\n";
echo "Setting uploaded_images to: " . $correctValue . "\n\n";

DB::table('seller_requests')
    ->where('id', 21)
    ->update(['uploaded_images' => $correctValue]);

echo "Done! Checking result...\n\n";

$raw = DB::table('seller_requests')->where('id', 21)->value('uploaded_images');
echo "New raw value:\n";
var_dump($raw);

echo "\n\nDecoded:\n";
var_dump(json_decode($raw, true));
