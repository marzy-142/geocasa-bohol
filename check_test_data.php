<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "Available Properties:\n";
$properties = Property::take(5)->get(['id', 'title', 'status', 'broker_id']);
foreach ($properties as $prop) {
    echo "  ID: {$prop->id}, Title: {$prop->title}, Status: {$prop->status}, Broker: {$prop->broker_id}\n";
}

echo "\nAvailable Buyers:\n";
$buyers = User::where('role', 'buyer')->take(5)->get(['id', 'name', 'email']);
foreach ($buyers as $buyer) {
    echo "  ID: {$buyer->id}, Name: {$buyer->name}, Email: {$buyer->email}\n";
}

echo "\nAvailable Brokers:\n";
$brokers = User::where('role', 'broker')->take(5)->get(['id', 'name', 'email']);
foreach ($brokers as $broker) {
    echo "  ID: {$broker->id}, Name: {$broker->name}, Email: {$broker->email}\n";
}
