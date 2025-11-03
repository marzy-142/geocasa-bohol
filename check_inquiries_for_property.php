<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inquiry;

$propertyId = 16;
$inquiries = Inquiry::with(['transaction'])->where('property_id',$propertyId)->orderBy('id','desc')->get();
foreach($inquiries as $inq){
    echo "Inquiry {$inq->id} status={$inq->status} completion_outcome=".($inq->completion_outcome ?? 'NULL')." tx_id=".($inq->transaction->id ?? 'NULL')."\n";
}
