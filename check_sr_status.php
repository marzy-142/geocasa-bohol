<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sr = DB::table('seller_requests')->where('id', 21)->first();
echo "Status: " . $sr->status . "\n";
echo "Property ID: " . ($sr->property_id ?? 'NULL') . "\n";
echo "Listed at: " . ($sr->listed_at ?? 'NULL') . "\n";
echo "Updated at: " . $sr->updated_at . "\n";
