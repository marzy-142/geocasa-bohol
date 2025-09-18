<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Client;

class TestInquirySeeder extends Seeder
{
    public function run()
    {
        // Get existing client users
        $users = User::where('role', 'client')->get();
        $property = Property::first();
        
        if (!$property) {
            $this->command->error('No properties found. Please create a property first.');
            return;
        }
        
        foreach ($users as $user) {
            // Create or get client record
            $client = Client::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
                
            if (!$client) {
                // Get a broker to assign
                $broker = User::where('role', 'broker')->first();
                
                $client = Client::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'broker_id' => $broker ? $broker->id : null,
                ]);
            } elseif (!$client->user_id) {
                $client->update(['user_id' => $user->id]);
            }
            
            // Create test inquiry
            Inquiry::create([
                'email' => $user->email,
                'name' => $user->name,
                'phone' => '123-456-7890',
                'message' => 'Test inquiry for visibility testing - ' . $user->name,
                'property_id' => $property->id,
                'user_id' => $user->id,
                'client_id' => $client->id,
                'status' => 'new'
            ]);
            
            $this->command->info("Created test inquiry for user: {$user->email}");
        }
    }
}