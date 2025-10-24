<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inquiry::class;

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
            'phone' => $this->faker->optional()->phoneNumber(),
            'message' => $this->faker->paragraph(),
            'inquiry_type' => $this->faker->randomElement(['general', 'viewing', 'purchase', 'information']),
            'property_id' => Property::factory(),
            'client_id' => $this->faker->optional()->randomElement([null, \App\Models\Client::factory()]),
            'assigned_broker_id' => null, // Will be set explicitly when needed
            'status' => $this->faker->randomElement(['new', 'contacted', 'scheduled', 'completed', 'closed']),
            'contacted_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'broker_notes' => $this->faker->optional()->paragraph(),
            'broker_response' => $this->faker->optional()->paragraph(),
            'responded_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the inquiry is new.
     */
    public function newInquiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
        ]);
    }

    /**
     * Indicate that the inquiry is contacted.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    /**
     * Indicate that the inquiry is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}