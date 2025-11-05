<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\Property;

$txs = Transaction::with(['property'])->orderBy('id','desc')->limit(20)->get();
foreach($txs as $t){
    echo "TX {$t->id} num={$t->transaction_number} status={$t->status} property_id={$t->property_id} property=".($t->property->title ?? 'N/A')."\n";
}
