<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Maria Santos Avatar Status ===" . PHP_EOL;
echo PHP_EOL;

$user = App\Models\User::where('email', 'maria@geocasabohol.com')->first();

if ($user) {
    echo "User: " . $user->name . PHP_EOL;
    echo "Avatar: " . ($user->avatar ?? 'NULL - No avatar uploaded') . PHP_EOL;
    echo PHP_EOL;
    
    if (!$user->avatar) {
        echo "ℹ️  The user has NOT uploaded an avatar yet." . PHP_EOL;
        echo "That's why you see the initials 'M' instead of a picture." . PHP_EOL;
        echo PHP_EOL;
        echo "To see a real avatar:" . PHP_EOL;
        echo "1. Login as maria@geocasabohol.com" . PHP_EOL;
        echo "2. Go to Account Settings → Profile tab" . PHP_EOL;
        echo "3. Upload a profile picture" . PHP_EOL;
        echo "4. The avatar will appear everywhere automatically!" . PHP_EOL;
    } else {
        echo "✓ Avatar exists: " . $user->avatar . PHP_EOL;
        $fullPath = storage_path('app/public/' . $user->avatar);
        if (file_exists($fullPath)) {
            echo "✓ File exists at: " . $fullPath . PHP_EOL;
        } else {
            echo "✗ File NOT found at: " . $fullPath . PHP_EOL;
        }
    }
}
