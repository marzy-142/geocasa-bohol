<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;

$titleLike = 'Bohol irrigated ricefield for sale in Ubay Bohol';
$prop = Property::where('title','like',"%$titleLike%")->first();
if(!$prop){
    echo "Not found\n"; exit;
}

echo "ID={$prop->id} title={$prop->title} status={$prop->status}\n";
