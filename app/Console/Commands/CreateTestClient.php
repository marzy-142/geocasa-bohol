<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class CreateTestClient extends Command
{
    protected $signature = 'create:test-client';
    protected $description = 'Create a test client user for testing the client interface';

    public function handle()
    {
        // Create broker user first
        $broker = User::create([
            'name' => 'Test Broker',
            'email' => 'broker@example.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => true,
            'email_verified_at' => now(),
            'prc_id' => 'PRC-TEST-001',
        ]);

        // Create client user
        $user = User::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);

        // Create client record
        $client = Client::create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
            'broker_id' => $broker->id,
            'budget_min' => 5000000,
            'budget_max' => 15000000,
            'preferred_location' => 'Panglao',
        ]);

        $this->info("✅ Test client created successfully!");
        $this->info("📧 Email: client@example.com");
        $this->info("🔑 Password: password");
        $this->info("👤 User ID: {$user->id}");
        $this->info("🏠 Client ID: {$client->id}");

        return 0;
    }
}