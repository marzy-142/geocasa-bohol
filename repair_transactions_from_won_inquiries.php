<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Support\Str;

$dry = in_array('--dry-run', $argv, true);

echo "=== Repair: Create Transactions for WON Inquiries (no transaction) ===\n";

echo 'Mode: '.($dry ? 'DRY-RUN' : 'APPLY')."\n\n";

$inquiries = Inquiry::with(['property','conversation'])
    ->where('status','completed')
    ->where('completion_outcome','won')
    ->whereDoesntHave('transaction')
    ->get();

echo 'Candidates: '.$inquiries->count()."\n";

$created = 0; $skipped = 0; $errors = 0;

foreach($inquiries as $inq){
    $prop = $inq->property;
    $brokerId = $inq->assigned_broker_id ?? ($prop?->broker_id);
    if(!$prop || !$brokerId){
        echo "- Skip inquiry {$inq->id}: missing property/broker\n";
        $skipped++; continue;
    }

    echo "- Create transaction for inquiry {$inq->id} | property {$prop->id} {$prop->title}\n";

    if($dry){ continue; }

    try{
        $txn = Transaction::create([
            'inquiry_id' => $inq->id,
            'property_id' => $prop->id,
            'client_id' => $inq->client_id,
            'broker_id' => $brokerId,
            'status' => 'offer_made',
            'transaction_number' => 'TXN-'.strtoupper(Str::random(8)),
            'offered_price' => $prop->total_price ?? 0,
            'inquiry_date' => $inq->created_at,
            'first_contact_date' => $inq->contacted_at,
            'viewing_date' => $inq->scheduled_at,
            'offer_date' => now(),
            'broker_notes' => "=== AUTO-CREATED FROM INQUIRY #{$inq->id} ===",
        ]);
        $inq->updateQuietly(['status' => 'in_transaction']);
        if(in_array($prop->status, ['available'], true)){
            $prop->updateQuietly(['status' => 'under_negotiation']);
        }
        if($inq->conversation){
            $inq->conversation->updateQuietly(['transaction_id'=>$txn->id]);
            Message::create([
                'conversation_id' => $inq->conversation->id,
                'sender_id' => null,
                'content' => "🎉 Transaction {$txn->transaction_number} has been created automatically.",
                'is_system_message' => true,
            ]);
        }
        event(new \App\Events\TransactionCreated($txn));
        $created++;
    }catch(\Throwable $e){
        echo "  ERROR: ".$e->getMessage()."\n";
        $errors++;
    }
}

echo "\nCreated: {$created} | Skipped: {$skipped} | Errors: {$errors}\n";
