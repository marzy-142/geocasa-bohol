<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class DebugPropertyTypes extends Command
{
    protected $signature = 'properties:debug-types';
    protected $description = 'Debug property types data';

    public function handle()
    {
        $this->info('=== Property Types Debug ===');
        $this->info('');

        // Get all properties
        $properties = Property::select('id', 'title', 'type', 'types', 'status')->get();

        $this->info("Total properties: " . $properties->count());
        $this->info('');

        // Show each property
        foreach ($properties as $prop) {
            $this->info("ID: {$prop->id}");
            $this->info("  Title: {$prop->title}");
            $this->info("  Status: {$prop->status}");
            $this->info("  Old type column: " . ($prop->type ?? 'NULL'));
            $this->info("  New types column: " . json_encode($prop->types));
            $this->info("  Types is array: " . (is_array($prop->types) ? 'YES' : 'NO'));
            
            if (is_array($prop->types)) {
                $this->info("  Types count: " . count($prop->types));
            }
            
            $this->info('');
        }

        // Test a query
        $this->info('=== Testing Query ===');
        $testType = 'residential_lot';
        $this->info("Testing filter for: {$testType}");
        
        $results = Property::whereJsonContains('types', $testType)->count();
        $this->info("Results with JSON contains: {$results}");
        
        $oldResults = Property::where('type', $testType)->count();
        $this->info("Results with old type column: {$oldResults}");
        
        return 0;
    }
}
