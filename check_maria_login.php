<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Maria Santos Account ===" . PHP_EOL;

$user = App\Models\User::where('email', 'maria@geocasabohol.com')->first();

if ($user) {
    echo "✓ User Found!" . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
    echo "Is Active: " . ($user->is_active ? 'Yes' : 'No') . PHP_EOL;
    echo "Deactivated At: " . ($user->deactivated_at ?? 'N/A') . PHP_EOL;
    echo "Deactivation Reason: " . ($user->deactivation_reason ?? 'N/A') . PHP_EOL;
    echo "Created At: " . $user->created_at . PHP_EOL;
    
    if (!$user->is_active) {
        echo PHP_EOL;
        echo "⚠️  PROBLEM: Account is DEACTIVATED!" . PHP_EOL;
        echo "This is why you can't login." . PHP_EOL;
        echo PHP_EOL;
        echo "Reactivating account..." . PHP_EOL;
        $user->is_active = true;
        $user->deactivated_at = null;
        $user->deactivation_reason = null;
        $user->save();
        echo "✓ Account reactivated successfully!" . PHP_EOL;
    } else {
        echo PHP_EOL;
        echo "✓ Account is ACTIVE - should be able to login" . PHP_EOL;
    }
} else {
    echo "✗ User NOT found with email: maria.santos@geocasa.com" . PHP_EOL;
    echo PHP_EOL;
    echo "Searching for similar emails..." . PHP_EOL;
    $users = App\Models\User::where('email', 'LIKE', '%maria%')->get();
    foreach ($users as $u) {
        echo "  - " . $u->email . " (" . $u->name . ")" . PHP_EOL;
    }
}
