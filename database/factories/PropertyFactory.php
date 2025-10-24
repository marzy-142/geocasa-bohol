<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['residential_lot', 'agricultural_land', 'commercial_lot', 'industrial_lot', 'beachfront', 'mountain_view', 'rice_field', 'coconut_plantation', 'subdivision_lot', 'titled_land', 'tax_declared']),
            'municipality' => $this->faker->randomElement(Property::BOHOL_MUNICIPALITIES),
            'barangay' => $this->faker->word(),
            'address' => $this->faker->address(),
            'lot_area_sqm' => $this->faker->randomFloat(2, 50, 5000),
            'price_per_sqm' => $this->faker->randomFloat(2, 500, 5000),
            'total_price' => function (array $attributes) {
                return round($attributes['lot_area_sqm'] * $attributes['price_per_sqm'], 2);
            },
            'title_type' => $this->faker->randomElement(['titled', 'tax_declared', 'mother_title', 'cct']),
            'title_number' => strtoupper($this->faker->bothify('T-#####')),
            'tax_declaration_number' => strtoupper($this->faker->bothify('TD-#####')),
            'zoning_classification' => $this->faker->word(),
            'coordinates_lat' => $this->faker->latitude(9.5, 9.8),  // Bohol range
            'coordinates_lng' => $this->faker->longitude(123.7, 124.5),
            'road_access' => $this->faker->boolean(),
            'electricity_available' => $this->faker->boolean(),
            'water_source' => $this->faker->boolean(),
            'internet_available' => $this->faker->boolean(),
            'nearby_landmarks' => [$this->faker->word(), $this->faker->word()],
            'status' => $this->faker->randomElement(['available', 'reserved', 'sold', 'under_negotiation', 'off_market']),
            'is_featured' => $this->faker->boolean(20),
            'broker_id' => User::factory(),

            // 🔹 Virtual Tour Fields
            'has_virtual_tour' => $this->faker->boolean(30), // 30% chance of having virtual tour
            'virtual_tour_images' => function (array $attributes) {
                return $attributes['has_virtual_tour']
                    ? ['tours/sample-360-' . $this->faker->numberBetween(1, 5) . '.jpg']
                    : null;
            },
            'tour_hotspots' => function (array $attributes) {
                if (!$attributes['has_virtual_tour']) return null;

                return [
                    [
                        'x' => $this->faker->numberBetween(10, 90),
                        'y' => $this->faker->numberBetween(10, 90),
                        'title' => $this->faker->randomElement(['Living Room', 'Kitchen', 'Bedroom', 'Bathroom']),
                        'description' => $this->faker->sentence(),
                    ]
                ];
            },
            'gis_data' => [
                'lat' => $this->faker->latitude(9.5, 9.8),
                'lng' => $this->faker->longitude(123.7, 124.5),
                'zoom' => $this->faker->numberBetween(12, 18),
            ],
        ];
    }
}
