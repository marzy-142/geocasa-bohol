<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement(['Tagbilaran', 'Panglao', 'Loboc', 'Carmen', 'Tubigon', 'Calape', 'Loon', 'Maribojoc']),
            'state' => 'Bohol',
            'zip_code' => $this->faker->postcode(),
            'budget_min' => $this->faker->randomFloat(2, 500000, 2000000),
            'budget_max' => function (array $attributes) {
                return $this->faker->randomFloat(2, $attributes['budget_min'], $attributes['budget_min'] * 3);
            },
            'preferred_location' => $this->faker->randomElement(['Tagbilaran', 'Panglao', 'Loboc', 'Carmen', 'Tubigon', 'Calape', 'Loon', 'Maribojoc']),
            'preferred_area_min' => $this->faker->randomFloat(2, 100, 500),
            'preferred_area_max' => function (array $attributes) {
                return $this->faker->randomFloat(2, $attributes['preferred_area_min'], $attributes['preferred_area_min'] * 2);
            },
            'preferred_features' => $this->faker->randomElements([
                'beachfront_access',
                'mountain_view',
                'road_access',
                'electricity_available',
                'water_source',
                'internet_available'
            ], $this->faker->numberBetween(1, 4)),
            'broker_id' => User::factory(),
            'source' => $this->faker->randomElement(['inquiry', 'manual', 'referral', 'website']),
            'notes' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'converted']),
        ];
    }

    /**
     * Indicate that the client is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the client is converted.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'converted',
        ]);
    }
}