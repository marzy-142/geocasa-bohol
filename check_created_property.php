<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the most recent property created for broker ID 4
$property = DB::table('properties')
    ->where('broker_id', 4)
    ->orderBy('created_at', 'desc')
    ->first();

echo "Most recent property for broker 4:\n";
print_r($property);
