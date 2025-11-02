<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;

echo "=== PROPERTIES UNDER TRANSACTION (Status or Active Tx) ===\n\n";

try {
    // Properties explicitly marked as under negotiation or reserved
    $byStatus = Property::query()
        ->whereIn('status', ['under_negotiation', 'reserved'])
        ->with(['broker'])
        ->select('properties.*')
        ->get();

    // Properties having at least one active (non-finalized, non-cancelled) transaction
    $byActiveTx = Property::query()
        ->whereHas('transactions', function ($q) {
            $q->whereNotIn('status', ['finalized', 'cancelled']);
        })
        ->with(['broker'])
        ->withCount([
            'transactions as active_transactions_count' => function ($q) {
                $q->whereNotIn('status', ['finalized', 'cancelled']);
            },
        ])
        ->select('properties.*')
        ->get();

    // Merge and unique by id
    $all = $byStatus->concat($byActiveTx)->unique('id')->values();

    // Focus: properties that are still marked 'available' but actually have active transactions
    $availableWithActiveTx = Property::query()
        ->where('status', 'available')
        ->whereHas('transactions', function ($q) {
            $q->whereNotIn('status', ['finalized', 'cancelled']);
        })
        ->with(['broker'])
        ->withCount([
            'transactions as active_transactions_count' => function ($q) {
                $q->whereNotIn('status', ['finalized', 'cancelled']);
            },
        ])
        ->select('properties.*')
        ->get();

    if ($all->isEmpty()) {
        echo "No properties currently under transaction found.\n";
        exit(0);
    }

    echo "Found {$all->count()} property/properties under transaction:\n\n";

    foreach ($all as $p) {
        $activeCount = property_exists($p, 'active_transactions_count') ? ($p->active_transactions_count ?? 0) : 0;
        $url = "/properties/{$p->slug}";
        echo "ID: {$p->id}\n";
        echo "Title: {$p->title}\n";
        echo "Status: {$p->status}" . ($activeCount > 0 ? "  (active tx: {$activeCount})" : "") . "\n";
        echo "Municipality: " . ($p->municipality ?? 'N/A') . "\n";
        echo "Broker: " . ($p->broker->name ?? 'N/A') . "\n";
        echo "Public URL: {$url}\n";
        echo str_repeat('-', 60) . "\n";
    }

    if ($availableWithActiveTx->isNotEmpty()) {
        echo "\n=== MISMATCH: AVAILABLE BUT HAS ACTIVE TRANSACTION ===\n\n";
        foreach ($availableWithActiveTx as $p) {
            $url = "/properties/{$p->slug}";
            echo "ID: {$p->id} | Title: {$p->title} | Active Tx: {$p->active_transactions_count} | Broker: " . ($p->broker->name ?? 'N/A') . " | URL: {$url}\n";
        }
    }

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
