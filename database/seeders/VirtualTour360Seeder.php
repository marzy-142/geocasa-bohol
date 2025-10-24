<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class VirtualTour360Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create a broker user
        $broker = User::where('role', 'broker')->first();
        
        if (!$broker) {
            $broker = User::create([
                'name' => 'Demo Broker',
                'email' => 'broker360@test.com',
                'password' => bcrypt('password'),
                'role' => 'broker',
                'application_status' => 'approved',
                'is_approved' => true
            ]);
        }

        // Sample 360-degree equirectangular image URLs (stable demo sources)
        // Using providers known to allow hotlinking and with proper CORS headers
        $sample360Images = [
            'https://pannellum.org/images/alma.jpg',
            'https://photo-sphere-viewer-data.netlify.app/assets/sphere.jpg',
            'https://pannellum.org/images/cerro-toco-0.jpg',
        ];

        // Create properties with 360-degree virtual tours
        $properties = [
            [
                'title' => 'Luxury Beachfront Villa with 360° Tour - Panglao',
                'slug' => 'luxury-beachfront-villa-360-panglao',
                'description' => 'Experience this stunning beachfront property with our immersive 360-degree virtual tour. Walk through the pristine beach, crystal clear waters, and imagine your dream home or resort.',
                'type' => 'beachfront',
                'status' => 'available',
                'price_per_sqm' => 25000,
                'total_price' => 37500000,
                'address' => 'Alona Beach, Panglao Island',
                'municipality' => 'Panglao',
                'barangay' => 'Danao',
                'lot_area_sqm' => 1500,
                'lot_area_hectares' => 0.15,
                'title_type' => 'titled',
                'title_number' => 'T-360-001',
                'zoning_classification' => 'Residential/Commercial',
                'coordinates_lat' => 9.5500,
                'coordinates_lng' => 123.7500,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => true,
                'virtual_tour_images' => json_encode([$sample360Images[0]]),
                'gis_data' => json_encode([
                    'type' => 'beachfront',
                    'elevation' => '2m'
                ]),
                'broker_id' => $broker->id,
                'nearby_landmarks' => json_encode([
                    'Alona Beach',
                    'Hinagdanan Cave',
                    'Panglao Church'
                ]),
            ],
            [
                'title' => 'Mountain View Lot with 360° Panorama - Carmen',
                'slug' => 'mountain-view-lot-360-carmen',
                'description' => 'Explore the breathtaking Chocolate Hills view with our 360-degree virtual tour. This prime lot offers panoramic mountain vistas and fresh air.',
                'type' => 'mountain_view',
                'status' => 'available',
                'price_per_sqm' => 8000,
                'total_price' => 16000000,
                'address' => 'Chocolate Hills Complex, Carmen',
                'municipality' => 'Carmen',
                'barangay' => 'Poblacion',
                'lot_area_sqm' => 2000,
                'lot_area_hectares' => 0.2,
                'title_type' => 'titled',
                'title_number' => 'T-360-002',
                'zoning_classification' => 'Residential',
                'coordinates_lat' => 9.8167,
                'coordinates_lng' => 124.2000,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => true,
                'virtual_tour_images' => json_encode([$sample360Images[1]]),
                'gis_data' => json_encode([
                    'type' => 'mountain',
                    'elevation' => '150m'
                ]),
                'broker_id' => $broker->id,
                'nearby_landmarks' => json_encode([
                    'Chocolate Hills',
                    'Butterfly Sanctuary',
                    'Loboc River'
                ]),
            ],
            [
                'title' => 'Commercial Lot with Interactive 360° View - Tagbilaran',
                'slug' => 'commercial-lot-360-tagbilaran',
                'description' => 'Take a virtual walk around this prime commercial lot in the heart of Tagbilaran City. Perfect location for business establishments.',
                'type' => 'commercial_lot',
                'status' => 'available',
                'price_per_sqm' => 35000,
                'total_price' => 17500000,
                'address' => 'J.A. Clarin Street, Tagbilaran City',
                'municipality' => 'Tagbilaran City',
                'barangay' => 'Poblacion',
                'lot_area_sqm' => 500,
                'lot_area_hectares' => 0.05,
                'title_type' => 'titled',
                'title_number' => 'T-360-003',
                'zoning_classification' => 'Commercial',
                'coordinates_lat' => 9.6472,
                'coordinates_lng' => 123.8519,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => true,
                'virtual_tour_images' => json_encode([$sample360Images[2]]),
                'broker_id' => $broker->id,
                'nearby_landmarks' => json_encode([
                    'Tagbilaran Port',
                    'Island City Mall',
                    'BQ Mall'
                ]),
            ],
        ];

        foreach ($properties as $propertyData) {
            // Upsert by slug so re-running the seeder updates existing demo data
            Property::updateOrCreate(
                ['slug' => $propertyData['slug']],
                $propertyData
            );
        }

        $this->command->info('✅ Created 3 properties with 360-degree virtual tours!');
        $this->command->info('🌐 Properties with interactive 360° viewers are ready to explore.');
        $this->command->info('📍 Visit the property listings to see the immersive virtual tours.');
    }
}
