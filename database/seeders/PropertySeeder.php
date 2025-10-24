<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\User;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a broker user if it doesn't exist
        $broker = User::firstOrCreate(
            ['email' => 'broker@test.com'],
            [
                'name' => 'Test Broker',
                'password' => bcrypt('password'),
                'role' => 'broker',
                'is_approved' => true
            ]
        );

        // Create sample properties
        $properties = [
            [
                'title' => 'Beautiful Beachfront Lot in Panglao',
                'slug' => 'beachfront-lot-panglao-1',
                'description' => 'Stunning beachfront property with crystal clear waters and white sand beaches. Perfect for resort development or private residence.',
                'type' => 'beachfront',
                'status' => 'available',
                'price_per_sqm' => 15000,
                'total_price' => 15000000,
                'address' => 'Panglao Island, Bohol',
                'municipality' => 'Panglao',
                'barangay' => 'Danao',
                'lot_area_sqm' => 1000,
                'lot_area_hectares' => 0.1,
                'title_type' => 'titled',
                'title_number' => 'T-12345',
                'zoning_classification' => 'Residential',
                'coordinates_lat' => 9.5833,
                'coordinates_lng' => 123.8333,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => true,
                'broker_id' => $broker->id
            ],
            [
                'title' => 'Agricultural Land in Carmen',
                'slug' => 'agricultural-land-carmen-1',
                'description' => 'Fertile agricultural land perfect for farming or livestock. Has existing coconut trees and good soil quality.',
                'type' => 'agricultural_land',
                'status' => 'available',
                'price_per_sqm' => 500,
                'total_price' => 5000000,
                'address' => 'Carmen, Bohol',
                'municipality' => 'Carmen',
                'barangay' => 'Poblacion',
                'lot_area_sqm' => 10000,
                'lot_area_hectares' => 1.0,
                'title_type' => 'titled',
                'title_number' => 'T-67890',
                'zoning_classification' => 'Agricultural',
                'coordinates_lat' => 9.8167,
                'coordinates_lng' => 124.2000,
                'road_access' => true,
                'electricity_available' => false,
                'water_source' => true,
                'internet_available' => false,
                'is_featured' => false,
                'has_virtual_tour' => false,
                'broker_id' => $broker->id
            ],
            [
                'title' => 'Residential Lot in Tagbilaran City',
                'slug' => 'residential-lot-tagbilaran-1',
                'description' => 'Prime residential lot in the heart of Tagbilaran City. Close to schools, hospitals, and commercial areas.',
                'type' => 'residential_lot',
                'status' => 'available',
                'price_per_sqm' => 8000,
                'total_price' => 8000000,
                'address' => 'Tagbilaran City, Bohol',
                'municipality' => 'Tagbilaran City',
                'barangay' => 'Poblacion I',
                'lot_area_sqm' => 1000,
                'lot_area_hectares' => 0.1,
                'title_type' => 'titled',
                'title_number' => 'T-11111',
                'zoning_classification' => 'Residential',
                'coordinates_lat' => 9.6667,
                'coordinates_lng' => 123.8500,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => false,
                'broker_id' => $broker->id
            ],
            [
                'title' => 'Mountain View Property in Loboc',
                'slug' => 'mountain-view-loboc-1',
                'description' => 'Breathtaking mountain view property with cool climate and scenic landscapes. Perfect for eco-tourism or private retreat.',
                'type' => 'mountain_view',
                'status' => 'available',
                'price_per_sqm' => 3000,
                'total_price' => 3000000,
                'address' => 'Loboc, Bohol',
                'municipality' => 'Loboc',
                'barangay' => 'Poblacion',
                'lot_area_sqm' => 1000,
                'lot_area_hectares' => 0.1,
                'title_type' => 'titled',
                'title_number' => 'T-22222',
                'zoning_classification' => 'Residential',
                'coordinates_lat' => 9.6500,
                'coordinates_lng' => 124.0333,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => false,
                'has_virtual_tour' => true,
                'broker_id' => $broker->id
            ],
            [
                'title' => 'Commercial Lot in Dauis',
                'slug' => 'commercial-lot-dauis-1',
                'description' => 'Strategic commercial lot along the main highway. High visibility and foot traffic. Perfect for business establishment.',
                'type' => 'commercial_lot',
                'status' => 'available',
                'price_per_sqm' => 12000,
                'total_price' => 12000000,
                'address' => 'Dauis, Bohol',
                'municipality' => 'Dauis',
                'barangay' => 'Poblacion',
                'lot_area_sqm' => 1000,
                'lot_area_hectares' => 0.1,
                'title_type' => 'titled',
                'title_number' => 'T-33333',
                'zoning_classification' => 'Commercial',
                'coordinates_lat' => 9.6167,
                'coordinates_lng' => 123.8667,
                'road_access' => true,
                'electricity_available' => true,
                'water_source' => true,
                'internet_available' => true,
                'is_featured' => true,
                'has_virtual_tour' => false,
                'broker_id' => $broker->id
            ]
        ];

        foreach ($properties as $propertyData) {
            Property::create($propertyData);
        }

        $this->command->info('Created ' . count($properties) . ' sample properties');
    }
}