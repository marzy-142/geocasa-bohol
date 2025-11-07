<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SellerRequest;
use App\Models\Property;
use Illuminate\Support\Str;

$dry = in_array('--dry-run', $argv, true);

echo "=== Repair: Normalize Memorial Lot Properties ===\n";
echo 'Mode: '.($dry ? 'DRY-RUN' : 'APPLY')."\n\n";

$affected = 0; $fixed = 0; $skipped = 0; $errors = 0;

// Find properties linked to seller requests where type indicates memorial
$props = Property::query()
    ->where(function($q){
        $q->where('type','memorial_lot')
          ->orWhere('type','residential_lot') // potentially misclassified
          ->orWhere('type','commercial_lot');
    })
    ->get();

echo 'Candidates: '.$props->count()."\n";

foreach($props as $prop){
    try{
        // Try to locate the originating seller request if any
        $sr = SellerRequest::where('property_id', $prop->id)->first();
        $fromSr = false;
        if(!$sr){
            // Fallback: try by matching title and email
            $sr = SellerRequest::where('property_title', $prop->title)
                ->where('email', '!=', null)
                ->latest()->first();
        } else {
            $fromSr = true;
        }

        $isMemorial = false;
        if($sr){
            $t = $sr->property_type;
            if (is_string($t)) {
                $t = json_decode($t, true) ?: $t;
            }
            $vals = is_array($t) ? $t : [$t];
            $vals = array_map(function($v){
                $v = strtolower((string)$v);
                $v = str_replace([' ','-'],'_', $v);
                return $v; 
            }, $vals);
            $isMemorial = in_array('memorial', $vals, true) || in_array('memorial_lot', $vals, true) || in_array('memorial_park', $vals, true);
        }

        if(!$isMemorial){
            $skipped++; continue;
        }

        $affected++;
        echo "- Property #{$prop->id} {$prop->title}: set type=memorial_lot".($dry?" (dry)":"")."\n";

        // Backfill images if missing
    $currentImages = (array) ($prop->images ?? []);
    if(empty(array_filter($currentImages))){
            $imgSrc = null;
            if($sr){
                $imgSrc = $sr->uploaded_images ?: $sr->images ?: $sr->property_images;
            }
            $parse = function($val){
                if(!$val) return [];
                if(is_array($val)) return array_values(array_filter($val));
                if(is_string($val)){
                    $t = trim($val);
                    $first = json_decode($t, true);
                    if(is_array($first)) return array_values(array_filter($first));
                    if(is_string($first)){
                        $second = json_decode($first, true);
                        if(is_array($second)) return array_values(array_filter($second));
                    }
                    return $t ? [$t] : [];
                }
                if(is_object($val)) return array_values(array_filter((array)$val));
                return [];
            };
            $imgs = $parse($imgSrc);
            if(!empty($imgs)){
                echo "  + Backfilled ".count($imgs)." image(s) from seller request\n";
                if(!$dry){
                    $prop->images = $imgs;
                }
            }
        }

        if($dry){ continue; }

    $prop->type = 'memorial_lot';
    $prop->saveQuietly();
        $fixed++;
    }catch(\Throwable $e){
        echo "  ERROR: ".$e->getMessage()."\n";
        $errors++;
    }
}

echo "\nAffected: {$affected} | Fixed: {$fixed} | Skipped: {$skipped} | Errors: {$errors}\n";
