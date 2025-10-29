<?php

namespace Database\Seeders;

use App\Models\SellerRequest;
use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SellerRequestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🏠 Seeding Seller Requests...');

        // Get approved brokers for assignment
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->get();

        if ($brokers->isEmpty()) {
            $this->command->warn('No approved brokers found. Creating seller requests without broker assignments.');
        }

        // Sample seller request data
        $sellerRequests = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'phone' => '+63-917-123-4567',
                'address' => '123 Rizal Street, Poblacion 1',
                'property_title' => 'Beachfront Property in Panglao',
                'property_description' => 'Beautiful beachfront lot with stunning ocean views. Perfect for resort development or luxury vacation home. Direct beach access with pristine white sand and crystal clear waters. Ideal location near Alona Beach with great potential for tourism investment.',
                'property_type' => 'beachfront',
                'asking_price' => 25000000,
                'city' => 'Panglao',
                'province' => 'Bohol',
                'postal_code' => '6340',
                'lot_area' => 800,
                'features' => ['beachfront', 'ocean_view', 'white_sand_beach', 'coral_reefs', 'resort_potential'],
                'preferred_contact_method' => 'email',
                'availability' => 'weekdays',
                'urgency' => 'medium',
                'additional_notes' => 'Property has been in family for generations. Looking for serious buyers who will develop it properly.',
                'marketing_consent' => true,
                'newsletter_consent' => false,
                'terms_accepted' => true,
                'status' => 'under_review',
                'created_at' => now()->subDays(5),
                'assigned_broker_id' => $brokers->first()?->id,
            ],
            [
                'name' => 'Pedro Reyes',
                'email' => 'pedro.reyes@email.com',
                'phone' => '+63-919-555-7890',
                'address' => '456 Chocolate Hills Road, Carmen',
                'property_title' => 'Agricultural Land Near Chocolate Hills',
                'property_description' => 'Prime agricultural land with stunning views of the Chocolate Hills. Fertile soil perfect for rice farming, vegetable cultivation, or eco-tourism development. Property includes natural water source and has been used for farming for over 20 years.',
                'property_type' => 'agricultural_land',
                'asking_price' => 8500000,
                'city' => 'Carmen',
                'province' => 'Bohol',
                'postal_code' => '6319',
                'lot_area' => 12000,
                'features' => ['chocolate_hills_view', 'fertile_soil', 'natural_water_source', 'farming_history', 'eco_tourism_potential'],
                'preferred_contact_method' => 'phone',
                'availability' => 'weekends',
                'urgency' => 'low',
                'additional_notes' => 'Looking for buyers interested in sustainable farming or eco-tourism projects.',
                'marketing_consent' => true,
                'newsletter_consent' => true,
                'terms_accepted' => true,
                'status' => 'pending',
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'Ana Dela Cruz',
                'email' => 'ana.delacruz@email.com',
                'phone' => '+63-918-777-1234',
                'address' => '789 Burgos Street, Poblacion 2',
                'property_title' => 'Commercial Lot in Tagbilaran City Center',
                'property_description' => 'Prime commercial lot in the heart of Tagbilaran City. High foot traffic area perfect for retail, restaurant, or office development. Property is strategically located near the city hall, provincial capitol, and major shopping centers.',
                'property_type' => 'commercial_lot',
                'asking_price' => 18000000,
                'city' => 'Tagbilaran City',
                'province' => 'Bohol',
                'postal_code' => '6300',
                'lot_area' => 500,
                'features' => ['high_foot_traffic', 'city_center', 'near_government_offices', 'commercial_zone', 'development_ready'],
                'preferred_contact_method' => 'email',
                'availability' => 'anytime',
                'urgency' => 'high',
                'additional_notes' => 'Property is zoned for commercial use. All necessary permits can be easily obtained.',
                'marketing_consent' => true,
                'newsletter_consent' => true,
                'terms_accepted' => true,
                'status' => 'approved',
                'created_at' => now()->subDays(10),
                'assigned_broker_id' => $brokers->skip(1)->first()?->id,
                'reviewed_by' => 1, // Assuming admin user ID is 1
                'reviewed_at' => now()->subDays(8),
                'admin_notes' => 'Property meets all requirements. Ready for listing.',
            ],
            [
                'name' => 'Jose Garcia',
                'email' => 'jose.garcia@email.com',
                'phone' => '+63-920-888-5678',
                'address' => '321 Loboc River Road, Loboc',
                'property_title' => 'Residential Lot with River View',
                'property_description' => 'Beautiful residential lot overlooking the Loboc River. Property offers panoramic river views and is located in a peaceful residential area. Perfect for building a family home with natural surroundings and easy access to Loboc town center.',
                'property_type' => 'residential_lot',
                'asking_price' => 5500000,
                'city' => 'Loboc',
                'province' => 'Bohol',
                'postal_code' => '6326',
                'lot_area' => 750,
                'features' => ['river_view', 'peaceful_location', 'residential_area', 'near_town_center', 'natural_surroundings'],
                'preferred_contact_method' => 'phone',
                'availability' => 'weekdays_afternoon',
                'urgency' => 'medium',
                'additional_notes' => 'Property is perfect for families who want to live close to nature.',
                'marketing_consent' => false,
                'newsletter_consent' => false,
                'terms_accepted' => true,
                'status' => 'pending',
                'created_at' => now()->subDays(1),
            ],
            [
                'name' => 'Carmen Flores',
                'email' => 'carmen.flores@email.com',
                'phone' => '+63-921-999-3456',
                'address' => '654 Jagna Port Road, Jagna',
                'property_title' => 'Industrial Lot Near Port',
                'property_description' => 'Large industrial lot strategically located near Jagna Port. Ideal for warehouse, manufacturing, or logistics operations. Property has excellent road access and is close to the port for easy import/export operations.',
                'property_type' => 'industrial_lot',
                'asking_price' => 12500000,
                'city' => 'Jagna',
                'province' => 'Bohol',
                'postal_code' => '6308',
                'lot_area' => 2000,
                'features' => ['near_port', 'industrial_zone', 'excellent_road_access', 'warehouse_potential', 'logistics_ready'],
                'preferred_contact_method' => 'email',
                'availability' => 'weekdays_morning',
                'urgency' => 'high',
                'additional_notes' => 'Perfect for businesses looking to establish operations near the port.',
                'marketing_consent' => true,
                'newsletter_consent' => true,
                'terms_accepted' => true,
                'status' => 'rejected',
                'created_at' => now()->subDays(15),
                'assigned_broker_id' => $brokers->first()?->id,
                'reviewed_by' => 1,
                'reviewed_at' => now()->subDays(12),
                'admin_notes' => 'Property requires additional documentation for industrial use.',
                'rejection_reason' => 'Incomplete ownership documents and missing environmental clearance for industrial use.',
            ],
            [
                'name' => 'Roberto Santos',
                'email' => 'roberto.santos@email.com',
                'phone' => '+63-922-111-7890',
                'address' => '987 Danao Adventure Park Road, Danao',
                'property_title' => 'Mountain View Property',
                'property_description' => 'Spectacular mountain view property with breathtaking panoramic views of Bohol\'s mountain ranges. Property is perfect for eco-tourism development, mountain resort, or luxury residential development. Located near Danao Adventure Park.',
                'property_type' => 'mountain_view',
                'asking_price' => 32000000,
                'city' => 'Danao',
                'province' => 'Bohol',
                'postal_code' => '6344',
                'lot_area' => 1500,
                'features' => ['mountain_view', 'eco_tourism_potential', 'near_adventure_park', 'luxury_development', 'panoramic_views'],
                'preferred_contact_method' => 'phone',
                'availability' => 'weekends',
                'urgency' => 'low',
                'additional_notes' => 'Property offers unique mountain views and eco-tourism potential.',
                'marketing_consent' => true,
                'newsletter_consent' => false,
                'terms_accepted' => true,
                'status' => 'under_review',
                'created_at' => now()->subDays(7),
                'assigned_broker_id' => $brokers->skip(2)->first()?->id,
            ],
            [
                'name' => 'Lourdes Mendoza',
                'email' => 'lourdes.mendoza@email.com',
                'phone' => '+63-923-222-4567',
                'address' => '147 Ubay Agricultural Road, Ubay',
                'property_title' => 'Rice Field for Sale',
                'property_description' => 'Productive rice field with irrigation system. Property has been used for rice farming for over 30 years and has excellent soil quality. Includes irrigation system and storage facilities. Perfect for continued farming or agricultural development.',
                'property_type' => 'rice_field',
                'asking_price' => 6800000,
                'city' => 'Ubay',
                'province' => 'Bohol',
                'postal_code' => '6315',
                'lot_area' => 8000,
                'features' => ['irrigation_system', 'productive_soil', 'farming_history', 'storage_facilities', 'agricultural_ready'],
                'preferred_contact_method' => 'email',
                'availability' => 'weekdays_morning',
                'urgency' => 'medium',
                'additional_notes' => 'Looking for buyers who will continue agricultural use of the land.',
                'marketing_consent' => true,
                'newsletter_consent' => true,
                'terms_accepted' => true,
                'status' => 'pending',
                'created_at' => now()->subHours(12),
            ],
            [
                'name' => 'Antonio Cruz',
                'email' => 'antonio.cruz@email.com',
                'phone' => '+63-924-333-6789',
                'address' => '258 Subdivision Lot, Cortes',
                'property_title' => 'Subdivision Lot in Gated Community',
                'property_description' => 'Premium subdivision lot in a well-maintained gated community. Property is ready for immediate construction with all utilities available. Community has 24/7 security, clubhouse, and recreational facilities.',
                'property_type' => 'subdivision_lot',
                'asking_price' => 4200000,
                'city' => 'Cortes',
                'province' => 'Bohol',
                'postal_code' => '6321',
                'lot_area' => 400,
                'features' => ['gated_community', 'utilities_ready', 'security', 'clubhouse', 'recreational_facilities'],
                'preferred_contact_method' => 'phone',
                'availability' => 'weekends',
                'urgency' => 'low',
                'additional_notes' => 'Perfect for families looking for a secure residential environment.',
                'marketing_consent' => false,
                'newsletter_consent' => false,
                'terms_accepted' => true,
                'status' => 'listed',
                'created_at' => now()->subDays(20),
                'assigned_broker_id' => $brokers->first()?->id,
                'reviewed_by' => 1,
                'reviewed_at' => now()->subDays(18),
                'admin_notes' => 'Property approved and successfully converted to listing.',
                'property_id' => Property::first()?->id, // Link to existing property if available
                'listed_at' => now()->subDays(15),
            ]
        ];

        // Create seller requests
        foreach ($sellerRequests as $requestData) {
            $requestData = $this->addDefaultFields($requestData);
            SellerRequest::create($requestData);
            $this->command->info("✓ Created seller request: {$requestData['property_title']}");
        }

        // Create some additional pending requests for variety
        $additionalRequests = [
            [
                'name' => 'Sofia Reyes',
                'email' => 'sofia.reyes@email.com',
                'phone' => '+63-925-444-8901',
                'address' => '369 Balilihan Heritage Road, Balilihan',
                'property_title' => 'Heritage Property in Balilihan',
                'property_description' => 'Historic property with heritage value in Balilihan town center. Property includes original Spanish-era structures and is perfect for heritage tourism development or cultural center.',
                'property_type' => 'residential_lot',
                'asking_price' => 15800000,
                'city' => 'Balilihan',
                'province' => 'Bohol',
                'postal_code' => '6342',
                'lot_area' => 600,
                'features' => ['heritage_value', 'spanish_era_structures', 'town_center', 'cultural_potential', 'tourism_ready'],
                'preferred_contact_method' => 'email',
                'availability' => 'weekdays',
                'urgency' => 'medium',
                'additional_notes' => 'Property has historical significance and should be preserved.',
                'marketing_consent' => true,
                'newsletter_consent' => true,
                'terms_accepted' => true,
                'status' => 'pending',
                'created_at' => now()->subHours(6),
            ],
            [
                'name' => 'Miguel Torres',
                'email' => 'miguel.torres@email.com',
                'phone' => '+63-926-555-0123',
                'address' => '741 Raw Land Road, Bilar',
                'property_title' => 'Raw Land for Development',
                'property_description' => 'Raw land with development potential in Bilar. Property is undeveloped and offers flexibility for various types of development projects. Good road access and potential for residential or commercial development.',
                'property_type' => 'residential_lot',
                'asking_price' => 3200000,
                'city' => 'Bilar',
                'province' => 'Bohol',
                'postal_code' => '6317',
                'lot_area' => 1000,
                'features' => ['residential', 'development_potential', 'good_road_access', 'flexible_use', 'affordable'],
                'preferred_contact_method' => 'phone',
                'availability' => 'anytime',
                'urgency' => 'low',
                'additional_notes' => 'Perfect for investors looking for development opportunities.',
                'marketing_consent' => true,
                'newsletter_consent' => false,
                'terms_accepted' => true,
                'status' => 'pending',
                'created_at' => now()->subHours(3),
            ]
        ];

        foreach ($additionalRequests as $requestData) {
            $requestData = $this->addDefaultFields($requestData);
            SellerRequest::create($requestData);
            $this->command->info("✓ Created seller request: {$requestData['property_title']}");
        }

        // Add client-submitted seller requests (logged-in users)
        $this->command->info('');
        $this->command->info('👤 Adding client-submitted seller requests...');
        
        $clients = Client::whereNotNull('user_id')->get();
        
        if ($clients->isNotEmpty()) {
            $clientRequests = [
                [
                    'client_id' => $clients->first()->id,
                    'name' => $clients->first()->name,
                    'email' => $clients->first()->email,
                    'phone' => $clients->first()->phone ?? '+63-917-000-0001',
                    'property_type' => 'residential_lot',
                    'property_title' => '1200 sqm Land in Dauis, Totolan',
                    'property_description' => 'Flat land with road access. Near Bohol-Panglao International Airport. Ideal for residential or commercial development.',
                    'address' => 'Near Airport Road',
                    'city' => 'Dauis',
                    'province' => 'Bohol',
                    'municipality' => 'Dauis',
                    'barangay' => 'Totolan',
                    'lot_area' => 1200,
                    'asking_price' => 7200000,
                    'price_expectation' => 7200000,
                    'description' => 'Flat land with road access. Near Bohol-Panglao International Airport. Ideal for residential or commercial development.',
                    'contact_name' => $clients->first()->name,
                    'contact_email' => $clients->first()->email,
                    'contact_phone' => $clients->first()->phone ?? '+63-917-000-0001',
                    'preferred_contact_method' => 'both',
                    'urgency' => 'medium',
                    'status' => 'pending',
                    'submission_date' => now()->subHours(4),
                    'created_at' => now()->subHours(4),
                ],
                [
                    'client_id' => $clients->skip(1)->first()?->id ?? $clients->first()->id,
                    'name' => $clients->skip(1)->first()?->name ?? $clients->first()->name,
                    'email' => $clients->skip(1)->first()?->email ?? $clients->first()->email,
                    'phone' => $clients->skip(1)->first()?->phone ?? '+63-917-000-0002',
                    'property_type' => 'residential_lot',
                    'property_title' => '800 sqm Land in Tagbilaran, Booy',
                    'property_description' => 'Corner lot with utilities available. Perfect for building a family home or small business.',
                    'address' => 'Corner of Booy Street',
                    'city' => 'Tagbilaran City',
                    'province' => 'Bohol',
                    'municipality' => 'Tagbilaran City',
                    'barangay' => 'Booy',
                    'lot_area' => 800,
                    'asking_price' => 6400000,
                    'price_expectation' => 6400000,
                    'description' => 'Corner lot with utilities available. Perfect for building a family home or small business.',
                    'contact_name' => $clients->skip(1)->first()?->name ?? $clients->first()->name,
                    'contact_email' => $clients->skip(1)->first()?->email ?? $clients->first()->email,
                    'contact_phone' => $clients->skip(1)->first()?->phone ?? '+63-917-000-0002',
                    'preferred_contact_method' => 'email',
                    'urgency' => 'high',
                    'status' => 'under_review',
                    'submission_date' => now()->subDays(2),
                    'created_at' => now()->subDays(2),
                    'assigned_broker_id' => $brokers->first()?->id,
                ],
                [
                    'client_id' => $clients->skip(2)->first()?->id ?? $clients->first()->id,
                    'name' => $clients->skip(2)->first()?->name ?? $clients->first()->name,
                    'email' => $clients->skip(2)->first()?->email ?? $clients->first()->email,
                    'phone' => $clients->skip(2)->first()?->phone ?? '+63-917-000-0003',
                    'property_type' => 'residential_lot',
                    'property_title' => '2500 sqm Land in Panglao, Tawala',
                    'property_description' => 'Sloped terrain with ocean view. Great for resort or vacation home development.',
                    'address' => 'Tawala Beach Road',
                    'city' => 'Panglao',
                    'province' => 'Bohol',
                    'municipality' => 'Panglao',
                    'barangay' => 'Tawala',
                    'lot_area' => 2500,
                    'asking_price' => 15000000,
                    'price_expectation' => 15000000,
                    'description' => 'Sloped terrain with ocean view. Great for resort or vacation home development.',
                    'contact_name' => $clients->skip(2)->first()?->name ?? $clients->first()->name,
                    'contact_email' => $clients->skip(2)->first()?->email ?? $clients->first()->email,
                    'contact_phone' => $clients->skip(2)->first()?->phone ?? '+63-917-000-0003',
                    'preferred_contact_method' => 'phone',
                    'urgency' => 'low',
                    'status' => 'pending',
                    'submission_date' => now()->subHours(8),
                    'created_at' => now()->subHours(8),
                ],
            ];

            foreach ($clientRequests as $requestData) {
                $requestData = $this->addDefaultFields($requestData);
                SellerRequest::create($requestData);
                $this->command->info("✓ Created client-submitted request: {$requestData['property_title']}");
            }
        } else {
            $this->command->warn('No clients found. Skipping client-submitted requests.');
        }

        $this->command->info('');
        $this->command->info('🎉 Seller Requests seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • Total Seller Requests: ' . SellerRequest::count());
        $this->command->info('   • Client-Submitted: ' . SellerRequest::whereNotNull('client_id')->count());
        $this->command->info('   • Public Submissions: ' . SellerRequest::whereNull('client_id')->count());
        $this->command->info('   • Pending: ' . SellerRequest::where('status', 'pending')->count());
        $this->command->info('   • Under Review: ' . SellerRequest::where('status', 'under_review')->count());
        $this->command->info('   • Approved: ' . SellerRequest::where('status', 'approved')->count());
        $this->command->info('   • Rejected: ' . SellerRequest::where('status', 'rejected')->count());
        $this->command->info('   • Listed: ' . SellerRequest::where('status', 'listed')->count());
        $this->command->info('');
        $this->command->info('💰 Price Range: ₱3.2M - ₱32M');
        $this->command->info('🏠 Property Types: Land, Beachfront, Agricultural, Commercial, Residential, Industrial, Mountain View, Rice Field, Subdivision, Heritage');
        $this->command->info('📍 Locations: Various municipalities across Bohol');
    }

    /**
     * Add default fields to seller request data
     */
    private function addDefaultFields(array $data): array
    {
        $defaults = [
            'uploaded_images' => json_encode(['sample_property_1.jpg', 'sample_property_2.jpg']),
            'property_documents' => json_encode(['title_deed.pdf']),
            'ownership_documents' => json_encode(['tax_declaration.pdf']),
        ];

        return array_merge($defaults, $data);
    }
}
