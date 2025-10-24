<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimpleDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@geocasabohol.com'],
            [
                'name' => 'GeoCasa Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_approved' => true,
                'email_verified_at' => now(),
                'prc_id' => 'ADMIN-00000001',
            ]
        );

        // Create Demo Broker
        $broker = User::firstOrCreate(
            ['email' => 'maria@geocasabohol.com'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password'),
                'role' => 'broker',
                'is_approved' => true,
                'application_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'email_verified_at' => now(),
                'prc_id' => 'PRC-11111111',
                'city' => 'Tagbilaran City',
                'province' => 'Bohol',
                'address' => '123 Rizal Street, Tagbilaran City, Bohol',
                'phone' => '+63-917-123-4567',
                'years_experience' => 5,
                'brokerage_firm_name' => 'Santos Realty Group',
                'office_address' => '456 P. Burgos Street, Tagbilaran City',
                'office_contact_number' => '+63-38-501-1234',
                'postal_code' => '6300',
                'information_certified' => true,
                'prc_verification_consent' => true,
                'prc_verified' => true,
            ]
        );

        // Create a pending broker for demonstration
        User::firstOrCreate(
            ['email' => 'pending@geocasabohol.com'],
            [
                'name' => 'Pedro Reyes',
                'password' => Hash::make('password'),
                'role' => 'broker',
                'is_approved' => false,
                'application_status' => 'pending',
                'email_verified_at' => now(),
                'prc_id' => 'PRC-33333333',
                'city' => 'Carmen',
                'province' => 'Bohol',
                'address' => '321 Chocolate Hills Road, Carmen, Bohol',
                'phone' => '+63-919-555-7890',
                'years_experience' => 3,
                'brokerage_firm_name' => 'Carmen Land Development',
                'office_address' => 'Carmen Municipal Hall, Carmen, Bohol',
                'office_contact_number' => '+63-38-503-5555',
                'postal_code' => '6319',
                'information_certified' => true,
                'prc_verification_consent' => true,
                'prc_verified' => false, // Not verified yet
            ]
        );

        // Create Demo Properties
        $properties = [
            [
                'title' => 'Beachfront Paradise - Panglao Island',
                'description' => 'Stunning beachfront property with crystal clear waters and white sand beach. Perfect for resort development or luxury vacation home.',
                'type' => 'residential_lot',
                'municipality' => 'Panglao',
                'barangay' => 'Alona',
                'address' => 'Alona Beach, Panglao Island, Bohol',
                'status' => 'available',
                'price_per_sqm' => 25000,
                'total_price' => 15000000,
                'lot_area_sqm' => 600,
                'lot_area_hectares' => 0.06,
                'title_type' => 'titled',
                'title_number' => 'T-123456',
                'coordinates_lat' => 9.5701,
                'coordinates_lng' => 123.7744,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => true,
                'internet_available' => true,
                'nearby_landmarks' => json_encode(['Alona Beach', 'Panglao Island Nature Resort', 'Bohol Bee Farm']),
                'is_featured' => true,
                'images' => json_encode(['beachfront_0.jpg', 'beachfront_1.jpg', 'beachfront_2.jpg']),
                'broker_id' => $broker->id,
            ],
            [
                'title' => 'Mountain View Agricultural Land - Carmen',
                'description' => 'Prime agricultural land with stunning views of the Chocolate Hills. Ideal for farming, eco-tourism, or sustainable development.',
                'type' => 'agricultural_land',
                'municipality' => 'Carmen',
                'barangay' => 'Chocolate Hills',
                'address' => 'Chocolate Hills, Carmen, Bohol',
                'status' => 'available',
                'price_per_sqm' => 800,
                'total_price' => 8000000,
                'lot_area_sqm' => 10000,
                'lot_area_hectares' => 1.0,
                'title_type' => 'titled',
                'title_number' => 'OCT-789012',
                'coordinates_lat' => 9.9107,
                'coordinates_lng' => 124.1918,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => false,
                'internet_available' => false,
                'nearby_landmarks' => json_encode(['Chocolate Hills', 'Carmen Municipal Hall', 'Bohol Bee Farm']),
                'is_featured' => true,
                'images' => json_encode(['chocolate_hills_0.jpg', 'chocolate_hills_1.jpg']),
                'broker_id' => $broker->id,
            ],
            [
                'title' => 'Commercial Lot - Tagbilaran City Center',
                'description' => 'Prime commercial lot in the heart of Tagbilaran City. Perfect for retail, office, or mixed-use development.',
                'type' => 'commercial_lot',
                'municipality' => 'Tagbilaran City',
                'barangay' => 'Poblacion 1',
                'address' => 'Rizal Street, Tagbilaran City, Bohol',
                'status' => 'available',
                'price_per_sqm' => 35000,
                'total_price' => 21000000,
                'lot_area_sqm' => 600,
                'lot_area_hectares' => 0.06,
                'title_type' => 'titled',
                'title_number' => 'T-345678',
                'coordinates_lat' => 9.6728,
                'coordinates_lng' => 123.8625,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => true,
                'internet_available' => true,
                'nearby_landmarks' => json_encode(['Tagbilaran City Hall', 'Bohol Provincial Capitol', 'Island City Mall']),
                'is_featured' => false,
                'images' => json_encode(['commercial_0.jpg', 'commercial_1.jpg']),
                'broker_id' => $broker->id,
            ],
        ];

        foreach ($properties as $propertyData) {
            $property = Property::create($propertyData);
            // Ensure slug is generated if not provided
            if (empty($property->slug)) {
                $property->update(['slug' => \Illuminate\Support\Str::slug($property->title)]);
            }
        }

        // Create Demo Client
        $client = Client::create([
            'name' => 'Roberto Fernandez',
            'email' => 'roberto@example.com',
            'phone' => '+63-917-123-4567',
            'address' => '123 Rizal Street',
            'city' => 'Tagbilaran City',
            'state' => 'Bohol',
            'zip_code' => '6300',
            'budget_min' => 5000000,
            'budget_max' => 25000000,
            'preferred_location' => 'Panglao',
            'preferred_area_min' => 600,
            'preferred_area_max' => 2000,
            'broker_id' => $broker->id,
            'source' => 'inquiry',
            'status' => 'active',
        ]);

        // Create Demo Inquiries
        $property = Property::first();
        if ($property) {
            Inquiry::create([
                'name' => 'Roberto Fernandez',
                'email' => 'roberto@example.com',
                'phone' => '+63-917-123-4567',
                'message' => 'I am interested in this beachfront property for a resort development. Can we schedule a site visit?',
                'inquiry_type' => 'viewing',
                'property_id' => $property->id,
                'client_id' => $client->id,
                'status' => 'contacted',
                'contacted_at' => now()->subDays(2),
                'broker_notes' => 'Client is serious about resort development. Very interested in the location.',
                'broker_response' => 'Thank you for your interest! I can arrange a site visit this weekend. The property is perfect for resort development.',
                'responded_at' => now()->subDays(1),
            ]);
        }

        $this->command->info('Simple demo data created successfully!');
        $this->command->info('Admin: admin@geocasabohol.com / password');
        $this->command->info('Approved Broker: maria@geocasabohol.com / password');
        $this->command->info('Pending Broker: pending@geocasabohol.com / password');
    }
}
