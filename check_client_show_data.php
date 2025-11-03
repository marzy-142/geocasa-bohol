<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;

$client = Client::with(['inquiries.property','transactions.property'])
    ->withCount(['inquiries','transactions'])
    ->find(10);

if(!$client){ echo "Client not found\n"; exit; }

echo "Client #{$client->id} {$client->name}\n";
echo "inquiries_count={$client->inquiries_count} transactions_count={$client->transactions_count}\n";
$propertiesViewed = method_exists($client,'viewedProperties') ? $client->viewedProperties()->count() : 0;
echo "properties_viewed={$propertiesViewed}\n";
$recent = $client->inquiries()->with('property:id,title')->latest()->limit(5)->get(['id','property_id','created_at']);
echo "recent_inquiries count=".$recent->count()."\n";
foreach($recent as $inq){
    echo "- #{$inq->id} {$inq->property->title} at {$inq->created_at}\n";
}
