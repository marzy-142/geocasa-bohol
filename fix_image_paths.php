<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SellerRequest;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Image Paths ===\n\n";

$sellerRequests = SellerRequest::whereNotNull('uploaded_images')->get();

echo "Found " . $sellerRequests->count() . " seller requests with images\n\n";

foreach ($sellerRequests as $sr) {
    $rawImages = $sr->getRawOriginal('uploaded_images');
    
    echo "ID {$sr->id}: ";
    echo "Before: {$rawImages}\n";
    
    // Decode the JSON
    $images = json_decode($rawImages, true);
    
    if (is_array($images)) {
        // Re-encode without escaped slashes
        $fixedImages = json_encode($images, JSON_UNESCAPED_SLASHES);
        
        echo "After:  {$fixedImages}\n";
        
        // Update directly in database
        DB::table('seller_requests')
            ->where('id', $sr->id)
            ->update(['uploaded_images' => $fixedImages]);
        
        echo "✓ Fixed\n\n";
    } else {
        echo "✗ Skipped (not valid JSON array)\n\n";
    }
}

echo "Done!\n";
