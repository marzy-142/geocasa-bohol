<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:setup {--with-images : Download real property images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up GeoCasa Bohol demo environment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up GeoCasa Bohol Demo Environment...');
        $this->newLine();

        // Clear and optimize cache
        $this->info('📦 Optimizing application cache...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');

        // Clear existing demo data
        $this->info('🗑️ Clearing existing data...');
        $this->call('migrate:fresh', ['--force' => true]);

        // Create storage symlink
        $this->info('🔗 Creating storage symlink...');
        $this->call('storage:link');

        // Seed demo data
        if ($this->option('with-images')) {
            $this->info('🌱 Seeding complete demo data with real images...');
            $this->call('db:seed', ['--class' => 'CompleteDemoSeeder']);
        } else {
            $this->info('🌱 Seeding demo data with placeholder images...');
            $this->call('db:seed', ['--class' => 'SimpleDemoSeeder']);
            $this->info('🌱 Adding seller requests...');
            $this->call('db:seed', ['--class' => 'SellerRequestSeeder']);
        }

        // Set proper permissions
        $this->info('🔐 Setting permissions...');
        if (PHP_OS_FAMILY === 'Windows') {
            $this->warn('Windows detected - please manually set permissions if needed');
        } else {
            $this->exec('chmod -R 755 storage bootstrap/cache');
            $this->exec('chmod -R 755 public/storage');
        }

        // Clear and rebuild frontend assets
        $this->info('🎨 Building frontend assets...');
        $this->exec('npm run build');

        $this->newLine();
        $this->info('🎉 Demo setup complete!');
        $this->newLine();
        
        $this->info('📋 Demo credentials:');
        $this->info('   Admin: admin@geocasabohol.com / password');
        $this->info('   Approved Broker: maria@geocasabohol.com / password');
        $this->info('   Pending Broker: pending@geocasabohol.com / password');
        $this->newLine();
        
        $this->info('🌐 Start the server: php artisan serve');
        $this->info('🌐 Visit: http://localhost:8000');
        $this->newLine();
        
        $this->info('💡 Tip: Use --with-images flag to download real property images');
    }

    private function exec($command)
    {
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            $this->warn("Command failed: {$command}");
        }
        
        return $output;
    }
}
