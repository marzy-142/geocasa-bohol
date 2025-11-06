<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$raw = DB::table('seller_requests')->where('id', 21)->value('uploaded_images');

echo "Raw DB value:\n";
var_dump($raw);

echo "\n\nIs it already an array?\n";
echo gettype($raw) . "\n";

echo "\n\njson_decode with true:\n";
$decoded = json_decode($raw, true);
var_dump($decoded);

echo "\n\njson_decode without true:\n";
$decoded2 = json_decode($raw);
var_dump($decoded2);
