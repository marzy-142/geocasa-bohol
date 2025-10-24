<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data in proper order
        \DB::table('transactions')->delete();
        \DB::table('inquiries')->delete();
        \DB::table('properties')->delete();
        \DB::table('clients')->delete();
        User::where('role', '!=', 'admin')->delete();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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

        // Create Demo Brokers
        $brokers = collect([
            [
                'name' => 'Maria Santos',
                'email' => 'maria@geocasabohol.com',
                'prc_id' => 'PRC-11111111',
                'city' => 'Tagbilaran City',
                'province' => 'Bohol',
                'address' => '123 Rizal Street, Tagbilaran City, Bohol',
                'phone' => '+63-917-123-4567',
                'years_experience' => 5,
                'brokerage_firm_name' => 'Santos Realty Group',
                'office_address' => '456 P. Burgos Street, Tagbilaran City',
                'office_contact_number' => '+63-38-501-1234',
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@geocasabohol.com',
                'prc_id' => 'PRC-22222222',
                'city' => 'Panglao',
                'province' => 'Bohol',
                'address' => '789 Alona Beach Road, Panglao, Bohol',
                'phone' => '+63-918-987-6543',
                'years_experience' => 8,
                'brokerage_firm_name' => 'Bohol Beach Properties',
                'office_address' => 'Alona Beach, Panglao, Bohol',
                'office_contact_number' => '+63-38-502-9876',
            ],
            [
                'name' => 'Pedro Reyes',
                'email' => 'pedro@geocasabohol.com',
                'prc_id' => 'PRC-33333333',
                'city' => 'Carmen',
                'province' => 'Bohol',
                'address' => '321 Chocolate Hills Road, Carmen, Bohol',
                'phone' => '+63-919-555-7890',
                'years_experience' => 3,
                'brokerage_firm_name' => 'Carmen Land Development',
                'office_address' => 'Carmen Municipal Hall, Carmen, Bohol',
                'office_contact_number' => '+63-38-503-5555',
            ],
        ])->map(function ($brokerData) use ($admin) {
            return User::create([
                'name' => $brokerData['name'],
                'email' => $brokerData['email'],
                'password' => Hash::make('password'),
                'role' => 'broker',
                'is_approved' => true,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'email_verified_at' => now(),
                'prc_id' => $brokerData['prc_id'],
                'city' => $brokerData['city'],
                'province' => $brokerData['province'],
                'address' => $brokerData['address'],
                'phone' => $brokerData['phone'],
                'years_experience' => $brokerData['years_experience'],
                'brokerage_firm_name' => $brokerData['brokerage_firm_name'],
                'office_address' => $brokerData['office_address'],
                'office_contact_number' => $brokerData['office_contact_number'],
                'postal_code' => '6300',
                'information_certified' => true,
                'prc_verification_consent' => true,
            ]);
        });

        // Create Demo Properties
        $properties = collect([
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
                'images' => json_encode(['beachfront1.jpg', 'beachfront2.jpg', 'beachfront3.jpg']),
                'broker_id' => $brokers[1]->id,
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
                'images' => json_encode(['chocolate_hills1.jpg', 'chocolate_hills2.jpg']),
                'broker_id' => $brokers[2]->id,
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
                'images' => json_encode(['commercial1.jpg', 'commercial2.jpg']),
                'broker_id' => $brokers[0]->id,
            ],
            [
                'title' => 'Residential Subdivision Lot - Dauis',
                'description' => 'Peaceful residential lot in developing subdivision. Great for family home with modern amenities nearby.',
                'type' => 'residential_lot',
                'municipality' => 'Dauis',
                'barangay' => 'Marianos',
                'address' => 'Marianos, Dauis, Bohol',
                'status' => 'available',
                'price_per_sqm' => 12000,
                'total_price' => 4800000,
                'lot_area_sqm' => 400,
                'lot_area_hectares' => 0.04,
                'title_type' => 'titled',
                'title_number' => 'T-456789',
                'coordinates_lat' => 9.6250,
                'coordinates_lng' => 123.8667,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => true,
                'internet_available' => true,
                'nearby_landmarks' => json_encode(['Dauis Church', 'Panglao Bridge', 'Bohol International Airport']),
                'is_featured' => false,
                'images' => json_encode(['residential1.jpg', 'residential2.jpg']),
                'broker_id' => $brokers[0]->id,
            ],
            [
                'title' => 'Agricultural Land with Coconut Trees - Tubigon',
                'description' => 'Fertile agricultural land with mature coconut trees. Perfect for coconut farming or agricultural investment.',
                'type' => 'agricultural_land',
                'municipality' => 'Tubigon',
                'barangay' => 'Poblacion',
                'address' => 'Poblacion, Tubigon, Bohol',
                'status' => 'available',
                'price_per_sqm' => 600,
                'total_price' => 3000000,
                'lot_area_sqm' => 5000,
                'lot_area_hectares' => 0.5,
                'title_type' => 'titled',
                'title_number' => 'OCT-567890',
                'coordinates_lat' => 9.9500,
                'coordinates_lng' => 123.9667,
                'road_access' => true,
                'water_source' => true,
                'electricity_available' => false,
                'internet_available' => false,
                'nearby_landmarks' => json_encode(['Tubigon Port', 'Tubigon Municipal Hall', 'Cebu-Bohol Ferry Terminal']),
                'is_featured' => false,
                'images' => json_encode(['agricultural1.jpg', 'agricultural2.jpg']),
                'broker_id' => $brokers[2]->id,
            ],
        ])->map(function ($propertyData) {
            return Property::create($propertyData);
        });

        // Create Demo Clients
        $clients = collect([
            [
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
                'broker_id' => $brokers[1]->id,
                'source' => 'inquiry',
                'status' => 'active',
            ],
            [
                'name' => 'Elena Villanueva',
                'email' => 'elena@example.com',
                'phone' => '+63-918-987-6543',
                'address' => '456 Mabini Street',
                'city' => 'Tagbilaran City',
                'state' => 'Bohol',
                'zip_code' => '6300',
                'budget_min' => 10000000,
                'budget_max' => 50000000,
                'preferred_location' => 'Carmen',
                'preferred_area_min' => 1000,
                'preferred_area_max' => 5000,
                'broker_id' => $brokers[2]->id,
                'source' => 'manual',
                'status' => 'active',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael@example.com',
                'phone' => '+63-919-555-7890',
                'address' => '789 Luna Street',
                'city' => 'Tagbilaran City',
                'state' => 'Bohol',
                'zip_code' => '6300',
                'budget_min' => 3000000,
                'budget_max' => 8000000,
                'preferred_location' => 'Dauis',
                'preferred_area_min' => 300,
                'preferred_area_max' => 800,
                'broker_id' => $brokers[0]->id,
                'source' => 'inquiry',
                'status' => 'active',
            ],
        ])->map(function ($clientData) {
            return Client::create($clientData);
        });

        // Create Demo Inquiries
        $inquiries = collect([
            [
                'name' => 'Roberto Fernandez',
                'email' => 'roberto@example.com',
                'phone' => '+63-917-123-4567',
                'message' => 'I am interested in this beachfront property for a resort development. Can we schedule a site visit?',
                'inquiry_type' => 'viewing',
                'property_id' => $properties[0]->id,
                'client_id' => $clients[0]->id,
                'status' => 'contacted',
                'contacted_at' => now()->subDays(2),
                'broker_notes' => 'Client is serious about resort development. Very interested in the location.',
                'broker_response' => 'Thank you for your interest! I can arrange a site visit this weekend. The property is perfect for resort development.',
                'responded_at' => now()->subDays(1),
            ],
            [
                'name' => 'Elena Villanueva',
                'email' => 'elena@example.com',
                'phone' => '+63-918-987-6543',
                'message' => 'I would like to know more about the agricultural land near Chocolate Hills. What crops are suitable?',
                'inquiry_type' => 'information',
                'property_id' => $properties[1]->id,
                'client_id' => $clients[1]->id,
                'status' => 'new',
                'created_at' => now()->subHours(3),
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael@example.com',
                'phone' => '+63-919-555-7890',
                'message' => 'Is the commercial lot still available? I am interested in setting up a retail business.',
                'inquiry_type' => 'information',
                'property_id' => $properties[2]->id,
                'client_id' => $clients[2]->id,
                'status' => 'completed',
                'broker_notes' => 'Client has retail business experience. Good potential buyer.',
                'broker_response' => 'Yes, the lot is still available! It\'s perfect for retail business with high foot traffic in the area.',
                'responded_at' => now()->subHours(1),
            ],
        ])->map(function ($inquiryData) {
            return Inquiry::create($inquiryData);
        });

        // Create Demo Transactions
        $transactions = collect([
            [
                'property_id' => $properties[0]->id,
                'client_id' => $clients[0]->id,
                'broker_id' => $brokers[1]->id,
                'inquiry_id' => $inquiries[0]->id,
                'offered_price' => 14500000,
                'final_price' => 14000000,
                'commission_rate' => 3.0,
                'commission_amount' => 420000,
                'status' => 'negotiation',
                'created_at' => now()->subDays(1),
            ],
            [
                'property_id' => $properties[2]->id,
                'client_id' => $clients[2]->id,
                'broker_id' => $brokers[0]->id,
                'inquiry_id' => $inquiries[2]->id,
                'offered_price' => 20000000,
                'final_price' => 19500000,
                'commission_rate' => 3.0,
                'commission_amount' => 585000,
                'status' => 'finalized',
                'finalized_date' => now()->subDays(5),
                'created_at' => now()->subDays(10),
            ],
        ])->map(function ($transactionData) {
            return Transaction::create($transactionData);
        });

        $this->command->info('Demo data created successfully!');
        $this->command->info('Admin: admin@geocasabohol.com / password');
        $this->command->info('Broker: maria@geocasabohol.com / password');
        $this->command->info('Broker: juan@geocasabohol.com / password');
        $this->command->info('Broker: pedro@geocasabohol.com / password');
    }
}
