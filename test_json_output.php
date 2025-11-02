<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;

echo "=== PROPERTY JSON SERIALIZATION TEST ===\n\n";

$property = Property::with('broker')->first();

echo "Property as it would be sent to frontend (JSON):\n\n";

$json = $property->toArray();

echo "main_image: " . ($json['main_image'] ?? 'NOT SET') . "\n";
echo "images: " . json_encode($json['images'] ?? 'NOT SET') . "\n\n";

echo "Full JSON:\n";
echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
