<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'client_id' => Client::factory(),
            'broker_id' => User::factory(),
            'transaction_type' => $this->faker->randomElement(['sale', 'rent', 'lease']),
            'status' => $this->faker->randomElement([
                'inquiry',
                'initial_contact',
                'property_viewing',
                'offer_made',
                'negotiation',
                'offer_accepted',
                'contract_signed',
                'due_diligence',
                'financing',
                'closing_preparation',
                'finalized',
                'cancelled'
            ]),
            'offered_price' => $this->faker->randomFloat(2, 100000, 5000000),
            'final_price' => function (array $attributes) {
                return $this->faker->optional(0.7)->randomFloat(2, $attributes['offered_price'] * 0.9, $attributes['offered_price'] * 1.1);
            },
            'commission_rate' => $this->faker->randomFloat(4, 0.01, 0.10), // 1% to 10%
            'commission_amount' => function (array $attributes) {
                $price = $attributes['final_price'] ?? $attributes['offered_price'];
                return $price * $attributes['commission_rate'];
            },
            'inquiry_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'first_contact_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'viewing_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'offer_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'acceptance_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'contract_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'closing_date' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
            'finalized_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'transaction_number' => 'TXN-' . strtoupper($this->faker->bothify('???????')),
            'broker_notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the transaction is in inquiry status.
     */
    public function inquiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inquiry',
        ]);
    }

    /**
     * Indicate that the transaction is finalized.
     */
    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finalized',
            'finalized_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is for a sale.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'sale',
        ]);
    }

    /**
     * Indicate that the transaction is for a rent.
     */
    public function rent(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'rent',
        ]);
    }
}