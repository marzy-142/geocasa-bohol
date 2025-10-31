<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\SellerRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Setting up Complete GeoCasa Bohol Demo...');

        // First, seed the property images
        $this->call(PropertyImageSeeder::class);

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

        // Create Demo Brokers (Approved)
        $brokers = [];
        
        $maria = User::firstOrCreate(
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
        $brokers[] = $maria;

        $juan = User::firstOrCreate(
            ['email' => 'juan@geocasabohol.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password'),
                'role' => 'broker',
                'is_approved' => true,
                'application_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(30),
                'email_verified_at' => now()->subDays(30),
                'prc_id' => 'PRC-22222222',
                'city' => 'Panglao',
                'province' => 'Bohol',
                'address' => '789 Alona Beach Road, Panglao, Bohol',
                'phone' => '+63-918-555-1234',
                'years_experience' => 8,
                'brokerage_firm_name' => 'Dela Cruz Properties',
                'office_address' => 'Alona Beach Resort Area, Panglao',
                'office_contact_number' => '+63-38-502-5678',
                'postal_code' => '6340',
                'information_certified' => true,
                'prc_verification_consent' => true,
                'prc_verified' => true,
            ]
        );
        $brokers[] = $juan;

        $ana = User::firstOrCreate(
            ['email' => 'ana@geocasabohol.com'],
            [
                'name' => 'Ana Mendoza',
                'password' => Hash::make('password'),
                'role' => 'broker',
                'is_approved' => true,
                'application_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(15),
                'email_verified_at' => now()->subDays(15),
                'prc_id' => 'PRC-33333333',
                'city' => 'Carmen',
                'province' => 'Bohol',
                'address' => '456 Chocolate Hills Road, Carmen, Bohol',
                'phone' => '+63-919-777-8901',
                'years_experience' => 3,
                'brokerage_firm_name' => 'Mendoza Land Services',
                'office_address' => 'Carmen Municipal Plaza, Carmen',
                'office_contact_number' => '+63-38-503-9012',
                'postal_code' => '6319',
                'information_certified' => true,
                'prc_verification_consent' => true,
                'prc_verified' => true,
            ]
        );
        $brokers[] = $ana;

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
                'prc_id' => 'PRC-44444444',
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

        // Create Demo Properties with Real Images
        $properties = [
            [
                'title' => 'Beachfront Paradise - Panglao Island',
                'description' => 'Stunning beachfront property with crystal clear waters and white sand beach. Perfect for resort development or luxury vacation home. Features direct beach access, pristine coral reefs, and breathtaking sunset views.',
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
                'nearby_landmarks' => ['Alona Beach', 'Panglao Island Nature Resort', 'Bohol Bee Farm'],
                'is_featured' => true,
                'images' => ['beachfront_0.jpg', 'beachfront_1.jpg', 'beachfront_2.jpg'],
                'has_virtual_tour' => true,
                'virtual_tour_images' => ['https://photo-sphere-viewer-data.netlify.app/assets/sphere.jpg'],
                'broker_id' => $maria->id,
            ],
            [
                'title' => 'Mountain View Agricultural Land - Carmen',
                'description' => 'Prime agricultural land with stunning views of the Chocolate Hills. Ideal for farming, eco-tourism, or sustainable development. Features fertile soil, natural water sources, and panoramic hill views.',
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
                'nearby_landmarks' => ['Chocolate Hills', 'Carmen Municipal Hall', 'Bohol Bee Farm'],
                'is_featured' => true,
                'images' => ['chocolate_hills_0.jpg', 'chocolate_hills_1.jpg'],
                'broker_id' => $ana->id,
            ],
            [
                'title' => 'Commercial Lot - Tagbilaran City Center',
                'description' => 'Prime commercial lot in the heart of Tagbilaran City. Perfect for retail, office, or mixed-use development. High foot traffic area with excellent accessibility and business opportunities.',
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
                'nearby_landmarks' => ['Tagbilaran City Hall', 'Bohol Provincial Capitol', 'Island City Mall'],
                'is_featured' => false,
                'images' => ['commercial_0.jpg', 'commercial_1.jpg'],
                'broker_id' => $juan->id,
            ],
            [
                'title' => 'Residential Lot with Ocean View - Dauis',
                'description' => 'Beautiful residential lot with partial ocean views in Dauis. Perfect for building a family home with proximity to the beach and Dauis Church. Quiet residential area with good community.',
                'type' => 'residential_lot',
                'municipality' => 'Dauis',
                'barangay' => 'Poblacion',
                'address' => 'Dauis Church Road, Dauis, Bohol',
                'status' => 'available',
                'price_per_sqm' => 18000,
                'total_price' => 7200000,
                'lot_area_sqm' => 400,
                'lot_area_hectares' => 0.04,
                'title_type' => 'titled',
                'title_number' => 'T-901234',
                'coordinates_lat' => 9.6234,
                'coordinates_lng' => 123.8567,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => true,
                'internet_available' => true,
                'nearby_landmarks' => ['Dauis Church', 'Dauis Market', 'Beach Access'],
                'is_featured' => false,
                'images' => ['residential_0.jpg', 'residential_1.jpg'],
                'broker_id' => $maria->id,
            ],
            [
                'title' => 'Agricultural Land - Ubay',
                'description' => 'Productive agricultural land in Ubay with irrigation system. Perfect for rice farming, vegetable cultivation, or livestock. Property has been used for farming for over 20 years.',
                'type' => 'agricultural_land',
                'municipality' => 'Ubay',
                'barangay' => 'Poblacion',
                'address' => 'Ubay Agricultural Road, Ubay, Bohol',
                'status' => 'available',
                'price_per_sqm' => 850,
                'total_price' => 6800000,
                'lot_area_sqm' => 8000,
                'lot_area_hectares' => 0.8,
                'title_type' => 'titled',
                'title_number' => 'OCT-567890',
                'coordinates_lat' => 10.0567,
                'coordinates_lng' => 124.4789,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => false,
                'internet_available' => false,
                'nearby_landmarks' => ['Ubay Market', 'Irrigation System', 'Farming Area'],
                'is_featured' => false,
                'images' => ['agricultural_0.jpg', 'agricultural_1.jpg'],
                'broker_id' => $ana->id,
            ],
        ];

        foreach ($properties as $propertyData) {
            $property = Property::create($propertyData);
            // Ensure slug is generated if not provided
            if (empty($property->slug)) {
                $property->update(['slug' => \Illuminate\Support\Str::slug($property->title)]);
            }
            $this->command->info("✓ Created property: {$property->title}");
        }

        // Create Demo Clients
        $clients = [];
        
        $client1 = Client::create([
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
            'broker_id' => $maria->id,
            'source' => 'inquiry',
            'status' => 'active',
        ]);
        $clients[] = $client1;

        $client2 = Client::create([
            'name' => 'Sofia Martinez',
            'email' => 'sofia@example.com',
            'phone' => '+63-918-555-1234',
            'address' => '456 Burgos Street',
            'city' => 'Tagbilaran City',
            'state' => 'Bohol',
            'zip_code' => '6300',
            'budget_min' => 3000000,
            'budget_max' => 12000000,
            'preferred_location' => 'Tagbilaran City',
            'preferred_area_min' => 400,
            'preferred_area_max' => 800,
            'broker_id' => $juan->id,
            'source' => 'referral',
            'status' => 'active',
        ]);
        $clients[] = $client2;

        // Create Demo Inquiries
        $property = Property::first();
        if ($property) {
            Inquiry::create([
                'name' => 'Roberto Fernandez',
                'email' => 'roberto@example.com',
                'phone' => '+63-917-123-4567',
                'message' => 'I am interested in this beachfront property for a resort development. Can we schedule a site visit? The location looks perfect for our eco-tourism project.',
                'inquiry_type' => 'viewing',
                'property_id' => $property->id,
                'client_id' => $client1->id,
                'status' => 'contacted',
                'contacted_at' => now()->subDays(2),
                'broker_notes' => 'Client is serious about resort development. Very interested in the location and has the budget.',
                'broker_response' => 'Thank you for your interest! I can arrange a site visit this weekend. The property is perfect for resort development with its pristine beach access.',
                'responded_at' => now()->subDays(1),
            ]);
        }

        // Now seed seller requests
        $this->call(SellerRequestSeeder::class);
        
        // Seed transactions for realistic analytics
        $this->call(TransactionSeeder::class);
        
        // Seed inquiries for realistic analytics
        $this->call(InquirySeeder::class);
        
        // Seed compliance reports
        $this->call(ComplianceReportSeeder::class);
        
        // Seed admin activity logs
        $this->call(AdminActivityLogSeeder::class);

        $this->command->info('');
        $this->command->info('🎉 Complete Demo Setup Finished!');
        $this->command->info('');
        $this->command->info('📋 Demo Credentials:');
        $this->command->info('   Admin: admin@geocasabohol.com / password');
        $this->command->info('   Broker 1: maria@geocasabohol.com / password');
        $this->command->info('   Broker 2: juan@geocasabohol.com / password');
        $this->command->info('   Broker 3: ana@geocasabohol.com / password');
        $this->command->info('   Pending Broker: pending@geocasabohol.com / password');
        $this->command->info('');
        $this->command->info('🏠 Demo Data Created:');
        $this->command->info('   • ' . Property::count() . ' Properties with real images');
        $this->command->info('   • ' . Client::count() . ' Clients');
        $this->command->info('   • ' . Inquiry::count() . ' Inquiries');
        $this->command->info('   • ' . SellerRequest::count() . ' Seller Requests');
        $this->command->info('   • ' . User::where('role', 'broker')->where('is_approved', true)->count() . ' Approved Brokers');
        $this->command->info('   • ' . User::where('role', 'broker')->where('is_approved', false)->count() . ' Pending Brokers');
        $this->command->info('');
        $this->command->info('🌐 Visit: http://localhost:8000');
    }
}
