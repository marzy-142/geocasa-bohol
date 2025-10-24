<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the broker we created earlier
        $broker = User::where('email', 'broker@test.com')->first();
        
        if (!$broker) {
            $this->command->error('Broker not found. Please run PropertySeeder first.');
            return;
        }

        // Create a test client user
        $user = User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Test Client',
                'password' => bcrypt('password'),
                'role' => 'client'
            ]
        );

        // Create client record
        $client = Client::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Test Client',
                'user_id' => $user->id,
                'broker_id' => $broker->id,
                'status' => 'active'
            ]
        );

        $this->command->info('Created test client user: ' . $user->email);
    }
}