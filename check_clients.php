<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking client records...\n\n";

$clients = App\Models\Client::with('broker')->get();

foreach ($clients as $client) {
    echo "Client: " . $client->name . " (ID: " . $client->id . ")\n";
    echo "  User ID: " . ($client->user_id ?? 'NULL') . "\n";
    echo "  Broker ID: " . ($client->broker_id ?? 'NULL') . "\n";
    echo "  Broker Name: " . ($client->broker ? $client->broker->name : 'No broker assigned') . "\n";
    echo "  ---\n";
}
