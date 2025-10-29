<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;

class ArchiveSoldProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:archive-sold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive properties that have been sold for more than 90 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for properties to archive...');

        $properties = Property::shouldBeArchived()->get();

        if ($properties->isEmpty()) {
            $this->info('No properties need to be archived.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($properties as $property) {
            $property->archive();
            $count++;
            
            $this->line("Archived: {$property->title} (Sold on {$property->sold_at->format('Y-m-d')})");
        }

        $this->info("Successfully archived {$count} properties.");

        return Command::SUCCESS;
    }
}
