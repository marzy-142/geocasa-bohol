<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Property;

echo "=== STATUS VALUES ANALYSIS ===\n\n";

// Get a broker
$broker = User::where('role', 'broker')
    ->where('application_status', 'approved')
    ->first();

if (!$broker) {
    echo "No approved broker found.\n";
    exit;
}

echo "Analyzing data for: {$broker->name} (ID: {$broker->id})\n";
echo str_repeat("=", 60) . "\n\n";

// Check property statuses
echo "PROPERTY STATUSES:\n";
$propertyStatuses = Property::where('broker_id', $broker->id)
    ->select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($propertyStatuses as $status) {
    echo "  {$status->status}: {$status->count}\n";
}
echo "\n";

// Check inquiry statuses
echo "INQUIRY STATUSES:\n";
$inquiryStatuses = Inquiry::whereHas('property', function($q) use ($broker) {
    $q->where('broker_id', $broker->id);
})
    ->select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($inquiryStatuses as $status) {
    echo "  {$status->status}: {$status->count}\n";
}
echo "\n";

// Check transaction statuses
echo "TRANSACTION STATUSES:\n";
$transactionStatuses = Transaction::where('broker_id', $broker->id)
    ->select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($transactionStatuses as $status) {
    echo "  {$status->status}: {$status->count}\n";
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "ANALYSIS COMPLETE\n";
