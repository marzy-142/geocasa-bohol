<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@landproperty.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_approved' => true,
            'email_verified_at' => now(),
            'prc_id' => 'ADMIN-00000001',
        ]);

        // Create a test broker for demonstration
        User::create([
            'name' => 'John Broker',
            'email' => 'broker@landproperty.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => true,
            'email_verified_at' => now(),
            'prc_id' => 'PRC-12345678',
        ]);

        // Create a pending broker for testing approval process
        User::create([
            'name' => 'Jane Pending',
            'email' => 'pending@landproperty.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => false,
            'email_verified_at' => now(),
            'prc_id' => 'PRC-87654321',
        ]);
    }
}
