<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestBroker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:test-broker {name?} {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an approved test broker account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Enter broker name', 'Test Broker');
        $email = $this->argument('email') ?? $this->ask('Enter broker email', 'testbroker@geocasabohol.com');
        
        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists!');
            return 1;
        }
        
        $broker = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 1, // Assuming admin user ID is 1
            'email_verified_at' => now(),
            'license_number' => 'TEST-' . strtoupper(substr(md5($email), 0, 8)),
            'company_name' => 'Test Realty Company',
            'company_address' => 'Test Address, Tagbilaran City, Bohol',
            'years_experience' => 5,
            'specialization' => json_encode(['residential', 'commercial']),
            'phone' => '+639123456789',
            'address' => 'Test Address, Tagbilaran City, Bohol',
            'prc_verified' => true,
            'business_permit_verified' => true,
        ]);
        
        $this->info('Test broker account created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $broker->name],
                ['Email', $broker->email],
                ['Password', 'password'],
                ['Role', $broker->role],
                ['Status', $broker->application_status],
                ['License Number', $broker->license_number],
                ['Company', $broker->company_name],
            ]
        );
        
        $this->info('You can now login with:');
        $this->info('Email: ' . $broker->email);
        $this->info('Password: password');
        
        return 0;
    }
}