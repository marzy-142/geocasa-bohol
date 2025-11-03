<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\Transaction;

$titleLike = 'Bohol irrigated ricefield for sale in Ubay Bohol';

$property = Property::with(['transactions'])->withCount([
    'transactions as active_transactions_count' => function($q){
        $q->whereNotIn('status', ['finalized','cancelled']);
    }
])->where('title','like',"%$titleLike%")
  ->first();

if(!$property){
    echo "Property not found by title search\n";
    $property = Property::first();
}

echo "Property: {$property->title}\n";
echo "Status: {$property->status}\n";
$activeCount = $property->active_transactions_count ?? null;
echo "active_transactions_count: ".var_export($activeCount,true)."\n";
echo "Computed is_under_transaction: ".($property->is_under_transaction ? 'YES' : 'NO')."\n";

$txs = $property->transactions;
echo "Total transactions: ".$txs->count()."\n";
foreach($txs as $t){
    echo " - TX {$t->id} status={$t->status}\n";
}
