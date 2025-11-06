<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Property;

class MigratePropertyTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:migrate-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing property type data to types JSON array';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting property types migration...');

        // Count properties that need migration
        $needsMigration = Property::whereNotNull('type')
            ->whereNull('types')
            ->count();

        if ($needsMigration === 0) {
            $this->info('✅ All properties already have types data. No migration needed.');
            return 0;
        }

        $this->info("Found {$needsMigration} properties that need migration.");

        // Migrate data
        $this->info('Migrating type to types array...');
        
        DB::table('properties')
            ->whereNotNull('type')
            ->whereNull('types')
            ->update([
                'types' => DB::raw("JSON_ARRAY(type)")
            ]);

        // Verify migration
        $migrated = Property::whereNotNull('types')->count();
        $stillNull = Property::whereNull('types')->count();

        $this->info('');
        $this->info('Migration Results:');
        $this->info("✅ Properties with types: {$migrated}");
        $this->info("⚠️  Properties without types: {$stillNull}");

        // Show sample
        $sample = Property::whereNotNull('types')->first();
        if ($sample) {
            $this->info('');
            $this->info('Sample property:');
            $this->info("  ID: {$sample->id}");
            $this->info("  Title: {$sample->title}");
            $this->info("  Old type: {$sample->type}");
            $this->info("  New types: " . json_encode($sample->types));
        }

        $this->info('');
        $this->info('✅ Migration complete!');
        
        return 0;
    }
}
