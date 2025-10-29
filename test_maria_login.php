<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Maria Santos Login ===" . PHP_EOL;
echo PHP_EOL;

$user = App\Models\User::where('email', 'maria@geocasabohol.com')->first();

if (!$user) {
    echo "✗ User not found!" . PHP_EOL;
    exit;
}

echo "User Details:" . PHP_EOL;
echo "  Name: " . $user->name . PHP_EOL;
echo "  Email: " . $user->email . PHP_EOL;
echo "  Role: " . $user->role . PHP_EOL;
echo "  Is Active: " . ($user->is_active ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Test password
echo "Testing password 'password'..." . PHP_EOL;
if (Hash::check('password', $user->password)) {
    echo "✓ Password 'password' is CORRECT" . PHP_EOL;
} else {
    echo "✗ Password 'password' is INCORRECT" . PHP_EOL;
    echo PHP_EOL;
    echo "Resetting password to 'password'..." . PHP_EOL;
    $user->password = Hash::make('password');
    $user->save();
    echo "✓ Password reset successfully!" . PHP_EOL;
}

echo PHP_EOL;
echo "You can now login with:" . PHP_EOL;
echo "  Email: maria@geocasabohol.com" . PHP_EOL;
echo "  Password: password" . PHP_EOL;
