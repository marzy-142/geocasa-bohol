<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = ['properties', 'inquiries', 'transactions', 'clients', 'users', 'seller_requests'];

foreach($tables as $table) {
    try {
        echo "\n=== $table table indexes ===\n";
        $indexes = collect(DB::select('SHOW INDEX FROM ' . $table))
            ->groupBy('Key_name')
            ->map(function($group) {
                return $group->pluck('Column_name')->toArray();
            });
        
        foreach($indexes as $indexName => $columns) {
            if($indexName !== 'PRIMARY') {
                echo $indexName . ': [' . implode(', ', $columns) . ']' . PHP_EOL;
            }
        }
    } catch(Exception $e) {
        echo $table . ': error - ' . $e->getMessage() . PHP_EOL;
    }
}