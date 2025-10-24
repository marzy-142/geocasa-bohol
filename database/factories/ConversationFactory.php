<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['inquiry', 'transaction', 'general']),
            'participants' => [], // Will be populated in configure method
            'last_message_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'is_archived' => $this->faker->boolean(20), // 20% chance of being archived
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Conversation $conversation) {
            $participants = $conversation->participants;
            
            // If no participants were provided, create default users
            if (empty($participants)) {
                $user1 = User::factory()->create();
                $user2 = User::factory()->create();
                $participants = [$user1->id, $user2->id];
                
                // Update the JSON participants field
                $conversation->update(['participants' => $participants]);
            }
            
            // Create the pivot table relationships for all participants
            $conversation->participantUsers()->attach($participants);
        });
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }

    /**
     * Indicate that the conversation is for an inquiry.
     */
    public function inquiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'inquiry',
        ]);
    }

    /**
     * Indicate that the conversation is for a transaction.
     */
    public function transaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transaction',
        ]);
    }
}