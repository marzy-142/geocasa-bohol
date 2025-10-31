<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get first broker
$broker = \App\Models\User::where('role', 'broker')->first();

if (!$broker) {
    echo "No broker found!\n";
    exit;
}

echo "=== BROKER PROFILE BEFORE UPDATE ===\n";
echo "Name: {$broker->name}\n";
echo "Email: {$broker->email}\n";
echo "Bio: " . ($broker->bio ?? 'Not set') . "\n";
echo "Specializations: " . json_encode($broker->specializations ?? []) . "\n";
echo "Service Areas: " . json_encode($broker->service_areas ?? []) . "\n";
echo "Website: " . ($broker->website ?? 'Not set') . "\n";
echo "Facebook: " . ($broker->facebook ?? 'Not set') . "\n";
echo "LinkedIn: " . ($broker->linkedin ?? 'Not set') . "\n";
echo "Availability: " . ($broker->availability_status ?? 'Not set') . "\n";
echo "\n";

// Simulate updating professional profile
echo "=== SIMULATING PROFILE UPDATE ===\n";
$updateData = [
    'bio' => 'Experienced real estate broker with 5+ years in Bohol\'s property market. I specialize in helping clients find their dream beachfront properties and investment opportunities.',
    'specializations' => ['residential', 'beach_resort', 'investment'],
    'service_areas' => ['Tagbilaran City', 'Panglao', 'Dauis', 'Baclayon'],
    'website' => 'https://boholrealestate.com',
    'facebook' => 'https://facebook.com/boholbroker',
    'linkedin' => 'https://linkedin.com/in/juandelacruz',
    'availability_status' => 'available',
];

$broker->update($updateData);
echo "✅ Profile updated successfully!\n\n";

// Refresh and display
$broker->refresh();

echo "=== BROKER PROFILE AFTER UPDATE ===\n";
echo "Name: {$broker->name}\n";
echo "Email: {$broker->email}\n";
echo "Bio: {$broker->bio}\n";
echo "Specializations: " . json_encode($broker->specializations) . "\n";
echo "Service Areas: " . json_encode($broker->service_areas) . "\n";
echo "Website: {$broker->website}\n";
echo "Facebook: {$broker->facebook}\n";
echo "LinkedIn: {$broker->linkedin}\n";
echo "Availability: {$broker->availability_status}\n";
echo "\n";

echo "=== TEST RESULTS ===\n";
echo "✅ Bio saved: " . (strlen($broker->bio) > 0 ? 'PASS' : 'FAIL') . "\n";
echo "✅ Specializations saved: " . (count($broker->specializations) === 3 ? 'PASS' : 'FAIL') . "\n";
echo "✅ Service areas saved: " . (count($broker->service_areas) === 4 ? 'PASS' : 'FAIL') . "\n";
echo "✅ Website saved: " . ($broker->website === 'https://boholrealestate.com' ? 'PASS' : 'FAIL') . "\n";
echo "✅ Facebook saved: " . ($broker->facebook === 'https://facebook.com/boholbroker' ? 'PASS' : 'FAIL') . "\n";
echo "✅ LinkedIn saved: " . ($broker->linkedin === 'https://linkedin.com/in/juandelacruz' ? 'PASS' : 'FAIL') . "\n";
echo "✅ Availability saved: " . ($broker->availability_status === 'available' ? 'PASS' : 'FAIL') . "\n";

echo "\n🎉 Professional Profile backend test complete!\n";
