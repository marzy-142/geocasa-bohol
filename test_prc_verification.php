<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$prcService = app(App\Services\PRCVerificationService::class);

echo "=== Testing PRC Mock Verification ===\n\n";

// Test case 1: Full name "Niño Jumao-as Caturza"
$fullName = "Niño Jumao-as Caturza";
$nameParts = explode(' ', $fullName);
$firstName = $nameParts[0] ?? '';
$lastName = implode(' ', array_slice($nameParts, 1)) ?: ($nameParts[0] ?? '');

echo "Test 1: Full name = '$fullName'\n";
echo "  Extracted firstName: '$firstName'\n";
echo "  Extracted lastName: '$lastName'\n";

$result1 = $prcService->mockVerification('123456', $lastName, $firstName);
echo "  Verification result: " . ($result1['verified'] ? 'VERIFIED ✓' : 'FAILED ✗') . "\n";
echo "  Details: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";

// Test case 2: Simple name "Pedro Reyes"
$fullName2 = "Pedro Reyes";
$nameParts2 = explode(' ', $fullName2);
$firstName2 = $nameParts2[0] ?? '';
$lastName2 = implode(' ', array_slice($nameParts2, 1)) ?: ($nameParts2[0] ?? '');

echo "Test 2: Full name = '$fullName2'\n";
echo "  Extracted firstName: '$firstName2'\n";
echo "  Extracted lastName: '$lastName2'\n";

$result2 = $prcService->mockVerification('654321', $lastName2, $firstName2);
echo "  Verification result: " . ($result2['verified'] ? 'VERIFIED ✓' : 'FAILED ✗') . "\n";
echo "  Details: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";

// Test case 3: Empty strings (old behavior)
echo "Test 3: Empty first/last names (old behavior)\n";
echo "  firstName: ''\n";
echo "  lastName: ''\n";

$result3 = $prcService->mockVerification('999999', '', '');
echo "  Verification result: " . ($result3['verified'] ? 'VERIFIED ✓' : 'FAILED ✗') . "\n";
echo "  Details: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
