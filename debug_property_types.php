<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$property = App\Models\Property::find(26);

echo "Property #26 types field:\n";
echo "Raw types from DB:\n";
var_dump($property->getRawOriginal('types'));

echo "\n\nCasted types:\n";
var_dump($property->types);

echo "\n\nformatted_types accessor:\n";
var_dump($property->formatted_types);
