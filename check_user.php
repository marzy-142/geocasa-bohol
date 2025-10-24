<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(89);

if ($user) {
    echo "User ID: " . $user->id . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
    echo "Is Approved: " . ($user->is_approved ? 'true' : 'false') . PHP_EOL;
    echo "Status: " . $user->status . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}