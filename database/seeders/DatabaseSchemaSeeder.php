<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\SellerRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSchemaSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'GeoCasa Admin',
            'email' => 'admin@geocasabohol.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);

        // Create approved brokers
        $broker1 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@geocasabohol.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);

        $broker2 = User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@geocasabohol.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Create pending broker
        $pendingBroker = User::create([
            'name' => 'Pedro Reyes',
            'email' => 'pedro@geocasabohol.com',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'is_approved' => false,
            'email_verified_at' => now(),
        ]);

        // Create regular client user
        $clientUser = User::create([
            'name' => 'Anna Garcia',
            'email' => 'anna@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);

        // Create GeoCasa Bohol sample properties
        $property1 = Property::create([
            'title' => 'Prime Beachfront Lot in Panglao Island',
            'slug' => 'prime-beachfront-lot-panglao-' . \Str::random(6),
            'description' => 'Stunning 2,500 sqm beachfront property with white sand beach access. Perfect for resort development or luxury residence. Clear title, with road access and utilities nearby.',
            'type' => 'beachfront',
            'status' => 'available',
            'price_per_sqm' => 8500.00,
            'total_price' => 21250000.00,
            'address' => 'Alona Beach Road, Tawala',
            'municipality' => 'Panglao',
            'barangay' => 'Tawala',
            'lot_area_sqm' => 2500.00,
            'lot_area_hectares' => 0.25,
            'title_type' => 'titled',
            'title_number' => 'TCT-12345',
            'coordinates_lat' => 9.5330,
            'coordinates_lng' => 123.8530,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => true,
            'internet_available' => true,
            'nearby_landmarks' => ['Alona Beach', 'Panglao Airport', 'Bohol Bee Farm'],
            'zoning_classification' => 'Tourism Zone',
            'images' => ['beachfront1.jpg', 'beachfront2.jpg', 'beachfront3.jpg'],
            'documents' => ['title_beachfront.pdf'],
            'is_featured' => true,
            'broker_id' => $broker1->id,
            // Virtual Tour Data
            'has_virtual_tour' => true,
            'virtual_tour_images' => [
                'beachfront_360_entrance.jpg',
                'beachfront_360_beach_view.jpg',
                'beachfront_360_property_center.jpg',
                'beachfront_360_sunset_view.jpg'
            ],
            'gis_data' => [
                'elevation' => 2.5,
                'soil_type' => 'sandy_loam',
                'flood_zone' => 'none',
                'environmental_clearance' => 'approved',
                'coastal_setback' => 20,
                'tide_level' => 'high_tide_safe'
            ],
            'tour_hotspots' => [
                [
                    'id' => 1,
                    'image_index' => 0,
                    'x_position' => 45.2,
                    'y_position' => 30.8,
                    'title' => 'Beach Access Point',
                    'description' => 'Direct access to pristine white sand beach',
                    'icon' => 'beach'
                ],
                [
                    'id' => 2,
                    'image_index' => 1,
                    'x_position' => 60.5,
                    'y_position' => 25.3,
                    'title' => 'Sunset Viewing Area',
                    'description' => 'Perfect spot for watching spectacular sunsets',
                    'icon' => 'sunset'
                ],
                [
                    'id' => 3,
                    'image_index' => 2,
                    'x_position' => 35.7,
                    'y_position' => 40.1,
                    'title' => 'Utility Connection Point',
                    'description' => 'Water and electricity connections available',
                    'icon' => 'utilities'
                ]
            ]
        ]);

        $property2 = Property::create([
            'title' => 'Agricultural Land in Carmen - Rice Field',
            'slug' => 'agricultural-land-carmen-' . \Str::random(6),
            'description' => 'Productive 3-hectare rice field in Carmen, Bohol. Ideal for agricultural investment or development. With irrigation system and farm-to-market road access.',
            'type' => 'rice_field',
            'status' => 'available',
            'price_per_sqm' => 450.00,
            'total_price' => 13500000.00,
            'address' => 'Sitio Malubog, Poblacion',
            'municipality' => 'Carmen',
            'barangay' => 'Poblacion',
            'lot_area_sqm' => 30000.00,
            'lot_area_hectares' => 3.0,
            'title_type' => 'tax_declared',
            'tax_declaration_number' => 'TD-2024-001',
            'coordinates_lat' => 9.8167,
            'coordinates_lng' => 124.0167,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => false,
            'internet_available' => false,
            'nearby_landmarks' => ['Carmen Public Market', 'Chocolate Hills', 'Mahogany Forest'],
            'zoning_classification' => 'Agricultural',
            'images' => ['ricefield1.jpg', 'ricefield2.jpg'],
            'documents' => ['tax_declaration.pdf'],
            'is_featured' => false,
            'broker_id' => $broker2->id,
        ]);

        $property3 = Property::create([
            'title' => 'Commercial Lot in Tagbilaran City Center',
            'slug' => 'commercial-lot-tagbilaran-' . \Str::random(6),
            'description' => 'Strategic 800 sqm commercial lot in the heart of Tagbilaran City. Perfect for business establishment, shopping center, or mixed-use development.',
            'type' => 'commercial_lot',
            'status' => 'available',
            'price_per_sqm' => 25000.00,
            'total_price' => 20000000.00,
            'address' => 'CPG Avenue, Poblacion 1',
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Poblacion 1',
            'lot_area_sqm' => 800.00,
            'lot_area_hectares' => 0.08,
            'title_type' => 'titled',
            'title_number' => 'TCT-67890',
            'coordinates_lat' => 9.6496,
            'coordinates_lng' => 123.8547,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => true,
            'internet_available' => true,
            'nearby_landmarks' => ['Tagbilaran City Hall', 'Island City Mall', 'Bohol Quality Mall'],
            'zoning_classification' => 'Commercial',
            'images' => ['commercial1.jpg'],
            'documents' => ['title_commercial.pdf', 'zoning_cert.pdf'],
            'is_featured' => true,
            'broker_id' => $broker1->id,
            // Virtual Tour Data
            'has_virtual_tour' => true,
            'virtual_tour_images' => [
                'commercial_360_street_view.jpg',
                'commercial_360_lot_center.jpg',
                'commercial_360_city_view.jpg'
            ],
            'gis_data' => [
                'elevation' => 15.2,
                'soil_type' => 'clay_loam',
                'flood_zone' => 'low_risk',
                'zoning_compliance' => 'commercial_approved',
                'building_height_limit' => 25,
                'parking_requirement' => '1_per_30sqm'
            ],
            'tour_hotspots' => [
                [
                    'id' => 1,
                    'image_index' => 0,
                    'x_position' => 50.0,
                    'y_position' => 35.0,
                    'title' => 'Main Street Frontage',
                    'description' => '20-meter frontage on busy CPG Avenue',
                    'icon' => 'road'
                ],
                [
                    'id' => 2,
                    'image_index' => 1,
                    'x_position' => 40.3,
                    'y_position' => 45.8,
                    'title' => 'Development Area',
                    'description' => 'Optimal space for commercial building construction',
                    'icon' => 'building'
                ]
            ]
        ]);

        $property4 = Property::create([
            'title' => 'Mountain View Residential Lot in Loboc',
            'slug' => 'mountain-view-loboc-' . \Str::random(6),
            'description' => 'Peaceful 1,200 sqm residential lot with stunning mountain views in Loboc. Perfect for building your dream home away from the city noise.',
            'type' => 'mountain_view',
            'status' => 'reserved',
            'price_per_sqm' => 1800.00,
            'total_price' => 2160000.00,
            'address' => 'Barangay Camayaan Hills',
            'municipality' => 'Loboc',
            'barangay' => 'Camayaan',
            'lot_area_sqm' => 1200.00,
            'lot_area_hectares' => 0.12,
            'title_type' => 'mother_title',
            'title_number' => 'OCT-11111',
            'coordinates_lat' => 9.6333,
            'coordinates_lng' => 124.0333,
            'road_access' => true,
            'water_source' => false,
            'electricity_available' => true,
            'internet_available' => false,
            'nearby_landmarks' => ['Loboc River', 'Loboc Church', 'Busay Falls'],
            'zoning_classification' => 'Residential',
            'images' => ['mountain1.jpg', 'mountain2.jpg'],
            'is_featured' => false,
            'broker_id' => $broker2->id,
        ]);

        $property5 = Property::create([
            'title' => 'Coconut Plantation in Ubay',
            'slug' => 'coconut-plantation-ubay-' . \Str::random(6),
            'description' => 'Productive 5-hectare coconut plantation with mature coconut trees. Generating steady income from copra production.',
            'type' => 'coconut_plantation',
            'status' => 'available',
            'price_per_sqm' => 350.00,
            'total_price' => 17500000.00,
            'address' => 'Sitio Kawayan, Poblacion',
            'municipality' => 'Ubay',
            'barangay' => 'Poblacion',
            'lot_area_sqm' => 50000.00,
            'lot_area_hectares' => 5.0,
            'title_type' => 'titled',
            'title_number' => 'TCT-55555',
            'coordinates_lat' => 10.0500,
            'coordinates_lng' => 124.4833,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => false,
            'internet_available' => false,
            'nearby_landmarks' => ['Ubay Port', 'Ubay Church', 'Kawasan Falls'],
            'zoning_classification' => 'Agricultural',
            'images' => ['coconut1.jpg'],
            'documents' => ['title_coconut.pdf'],
            'is_featured' => false,
            'broker_id' => $broker1->id,
        ]);

        // Create sample clients
        $client1 = Client::create([
            'name' => 'Roberto Fernandez',
            'email' => 'roberto@example.com',
            'phone' => '+63-917-123-4567',
            'address' => '123 Rizal Street',
            'city' => 'Tagbilaran City',
            'state' => 'Bohol',
            'zip_code' => '6300',
            'budget_min' => 5000000.00,
            'budget_max' => 25000000.00,
            'preferred_location' => 'Panglao',
            'preferred_area_min' => 1000.0,
            'preferred_area_max' => 5000.0,
            'broker_id' => $broker1->id,
            'source' => 'inquiry',
            'status' => 'active',
        ]);

        $client2 = Client::create([
            'name' => 'Elena Villanueva',
            'email' => 'elena@example.com',
            'phone' => '+63-918-987-6543',
            'budget_min' => 10000000.00,
            'budget_max' => 50000000.00,
            'preferred_location' => 'Carmen',
            'broker_id' => $broker2->id,
            'source' => 'manual',
            'status' => 'active',
        ]);

        // Create sample inquiries
        $inquiry1 = Inquiry::create([
            'name' => 'Roberto Fernandez',
            'email' => 'roberto@example.com',
            'phone' => '+63-917-123-4567',
            'message' => 'I am interested in this beachfront property for a resort development. Can we schedule a site visit?',
            'inquiry_type' => 'viewing',
            'property_id' => $property1->id,
            'client_id' => $client1->id,
            'status' => 'contacted',
            'contacted_at' => now()->subDays(2),
        ]);

        $inquiry2 = Inquiry::create([
            'name' => 'Michael Johnson',
            'email' => 'michael@example.com',
            'phone' => '+1-555-0123',
            'message' => 'What are the development restrictions for this commercial lot? Is it suitable for a hotel?',
            'inquiry_type' => 'information',
            'property_id' => $property3->id,
            'status' => 'new',
        ]);

        // Create sample transaction
        $transaction1 = Transaction::create([
            'property_id' => $property4->id,
            'client_id' => $client1->id,
            'broker_id' => $broker2->id,
            'inquiry_id' => $inquiry1->id,
            'offered_price' => 2000000.00,
            'final_price' => 2160000.00,
            'commission_rate' => 0.05,
            'commission_amount' => 108000.00,
            'status' => 'finalized',
            'inquiry_date' => now()->subDays(30),
            'first_contact_date' => now()->subDays(28),
            'viewing_date' => now()->subDays(25),
            'offer_date' => now()->subDays(20),
            'acceptance_date' => now()->subDays(18),
            'contract_date' => now()->subDays(15),
            'closing_date' => now()->subDays(5),
            'finalized_date' => now()->subDays(3),
            'broker_notes' => 'Client loved the mountain view. Quick decision maker.',
        ]);

        // Create sample seller requests
        $sellerRequest1 = SellerRequest::create([
            'seller_name' => 'Carmen Rodriguez',
            'seller_email' => 'carmen@example.com',
            'seller_phone' => '+63-919-555-0123',
            'property_title' => 'Subdivision Lot in Dauis',
            'property_description' => '500 sqm residential lot in a gated subdivision near Panglao bridge.',
            'asking_price' => 3500000.00,
            'property_area' => 500.0,
            'area_unit' => 'sqm',
            'property_location' => 'Dauis',
            'property_address' => 'Villa Esperanza Subdivision',
            'city' => 'Dauis',
            'state' => 'Bohol',
            'zip_code' => '6339',
            'property_type' => 'residential', // Changed from 'subdivision_lot' to 'residential'
            'features' => ['gated_community', 'near_airport', 'utilities_available'],
            'status' => 'pending',
        ]);
    }
}