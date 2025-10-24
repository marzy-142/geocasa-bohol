<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['text', 'file', 'system']),
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
            'attachments' => $this->faker->optional(0.2)->randomElements([
                ['type' => 'image', 'url' => $this->faker->imageUrl()],
                ['type' => 'file', 'url' => $this->faker->url(), 'name' => $this->faker->word() . '.pdf']
            ], 1),
        ];
    }

    /**
     * Indicate that the message is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the message is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the message is a system message.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'system',
            'content' => $this->faker->randomElement([
                'User joined the conversation',
                'User left the conversation',
                'Conversation was archived',
                'Transaction status updated'
            ]),
        ]);
    }

    /**
     * Indicate that the message has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                ['type' => 'image', 'url' => $this->faker->imageUrl()],
                ['type' => 'file', 'url' => $this->faker->url(), 'name' => $this->faker->word() . '.pdf']
            ],
        ]);
    }
}