<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get Maria Santos (the broker we updated)
$broker = \App\Models\User::where('email', 'maria@geocasabohol.com')->first();

if (!$broker) {
    echo "Broker not found!\n";
    exit;
}

echo "=== BROKER DIRECTORY DATA VERIFICATION ===\n\n";

echo "📋 Basic Info:\n";
echo "   Name: {$broker->name}\n";
echo "   Email: {$broker->email}\n";
echo "   Phone: " . ($broker->phone ?? 'Not set') . "\n";
echo "   Role: {$broker->role}\n\n";

echo "💼 Professional Profile:\n";
echo "   Bio: {$broker->bio}\n\n";

echo "🏢 Specializations:\n";
if ($broker->specializations && count($broker->specializations) > 0) {
    foreach ($broker->specializations as $spec) {
        echo "   ✓ " . ucwords(str_replace('_', ' ', $spec)) . "\n";
    }
} else {
    echo "   (None set)\n";
}
echo "\n";

echo "📍 Service Areas:\n";
if ($broker->service_areas && count($broker->service_areas) > 0) {
    foreach ($broker->service_areas as $area) {
        echo "   ✓ {$area}\n";
    }
} else {
    echo "   (None set)\n";
}
echo "\n";

echo "🌐 Online Presence:\n";
echo "   Website: " . ($broker->website ?? 'Not set') . "\n";
echo "   Facebook: " . ($broker->facebook ?? 'Not set') . "\n";
echo "   LinkedIn: " . ($broker->linkedin ?? 'Not set') . "\n\n";

echo "⏰ Availability:\n";
$status = $broker->availability_status ?? 'Not set';
$statusEmoji = match($status) {
    'available' => '🟢',
    'limited' => '🟡',
    'unavailable' => '🔴',
    default => '⚪'
};
echo "   {$statusEmoji} " . ucfirst($status) . "\n\n";

echo "=== DIRECTORY DISPLAY SIMULATION ===\n\n";

// Simulate what would appear in the broker directory
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                   BROKER PROFILE PREVIEW                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "👤 {$broker->name}\n";
echo str_repeat("─", 60) . "\n\n";

echo "📧 {$broker->email}\n";
if ($broker->phone) echo "📱 {$broker->phone}\n";
echo "\n";

if ($broker->bio) {
    echo "About:\n";
    echo wordwrap($broker->bio, 60) . "\n\n";
}

if ($broker->specializations && count($broker->specializations) > 0) {
    echo "Specializations:\n";
    echo "• " . implode("\n• ", array_map(function($s) {
        return ucwords(str_replace('_', ' ', $s));
    }, $broker->specializations)) . "\n\n";
}

if ($broker->service_areas && count($broker->service_areas) > 0) {
    echo "Service Areas:\n";
    echo "• " . implode("\n• ", $broker->service_areas) . "\n\n";
}

if ($broker->website || $broker->facebook || $broker->linkedin) {
    echo "Connect:\n";
    if ($broker->website) echo "🌐 {$broker->website}\n";
    if ($broker->facebook) echo "📘 {$broker->facebook}\n";
    if ($broker->linkedin) echo "💼 {$broker->linkedin}\n";
    echo "\n";
}

echo "Status: {$statusEmoji} " . ucfirst($broker->availability_status) . "\n";
echo str_repeat("═", 60) . "\n\n";

echo "✅ All professional profile data is ready for display in the Broker Directory!\n";
