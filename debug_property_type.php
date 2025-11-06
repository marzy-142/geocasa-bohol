<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$raw = DB::table('seller_requests')->where('id', 21)->value('property_type');

echo "Raw property_type from DB:\n";
var_dump($raw);

echo "\n\nType:\n";
echo gettype($raw) . "\n";

echo "\n\njson_decode result:\n";
$decoded = json_decode($raw, true);
var_dump($decoded);

echo "\n\nUsing model:\n";
$sr = App\Models\SellerRequest::find(21);
echo "property_type from model:\n";
var_dump($sr->property_type);

echo "\n\nType from model:\n";
echo gettype($sr->property_type) . "\n";

echo "\n\nIs array?\n";
echo (is_array($sr->property_type) ? 'YES' : 'NO') . "\n";

if (is_array($sr->property_type)) {
    echo "\nFirst element:\n";
    var_dump($sr->property_type[0]);
}
