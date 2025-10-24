<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Property;
use Carbon\Carbon;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('❓ Seeding Inquiries...');

        // Get properties and any users (clients)
        $properties = Property::all();
        $users = User::where('role', 'client')->get();

        if ($properties->isEmpty()) {
            $this->command->warn('⚠️  No properties found. Skipping inquiries...');
            return;
        }

        // If no client users, create some generic inquiries
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No client users found. Creating generic inquiries...');
        }

        $inquiries = [];
        $inquiryTypes = ['general', 'viewing', 'purchase', 'information'];
        $statuses = ['new', 'contacted', 'scheduled', 'completed', 'closed'];

        // Create inquiries for the last 60 days
        for ($i = 0; $i < 30; $i++) {
            $property = $properties->random();
            $user = $users->isNotEmpty() ? $users->random() : null;
            
            // Random date within last 60 days
            $createdAt = Carbon::now()->subDays(rand(0, 60))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            $inquiryType = $inquiryTypes[array_rand($inquiryTypes)];
            $status = $statuses[array_rand($statuses)];
            
            $inquiries[] = [
                'name' => $user ? $user->name : 'Anonymous User',
                'email' => $user ? $user->email : 'anonymous@example.com',
                'phone' => $user ? $user->phone : '+63-917-000-0000',
                'property_id' => $property->id,
                'client_id' => $user ? $user->id : null,
                'assigned_broker_id' => $property->broker_id,
                'inquiry_type' => $inquiryType,
                'status' => $status,
                'message' => $this->generateInquiryMessage($inquiryType, $property),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        // Insert inquiries
        Inquiry::insert($inquiries);

        $this->command->info('✅ Inquiries seeded successfully!');
        $this->command->info("📊 Summary:");
        $this->command->info("   • Total Inquiries: " . count($inquiries));
        $this->command->info("   • New: " . collect($inquiries)->where('status', 'new')->count());
        $this->command->info("   • Completed: " . collect($inquiries)->where('status', 'completed')->count());
        $this->command->info("   • Purchase Inquiries: " . collect($inquiries)->where('inquiry_type', 'purchase')->count());
    }

    private function generateInquiryMessage($type, $property)
    {
        $messages = [
            'general' => [
                "I'm interested in learning more about this property. Can you provide additional details?",
                "What are the key features of this property?",
                "Is this property still available?",
                "I'd like to know more about the neighborhood and amenities nearby.",
            ],
            'viewing' => [
                "I'd like to schedule a property viewing. When would be convenient?",
                "Can I arrange a site visit to see this property?",
                "I'm interested in viewing this property. What are the available times?",
                "Would it be possible to see this property in person?",
            ],
            'purchase' => [
                "I'm seriously considering purchasing this property. What's the best price you can offer?",
                "I'm ready to make an offer. What's the next step?",
                "This property looks perfect for my needs. How do we proceed with the purchase?",
                "I'm interested in buying this property. Can we discuss the terms?",
            ],
            'information' => [
                "What are the property taxes for this area?",
                "Are there any restrictions or HOA fees?",
                "What utilities are available at this property?",
                "Can you provide information about the property's history?",
            ],
        ];

        $typeMessages = $messages[$type] ?? ['I have a question about this property.'];
        return $typeMessages[array_rand($typeMessages)];
    }
}
