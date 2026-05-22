<?php

namespace Database\Factories;

use App\Models\ChatbotConversation;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChatbotConversationFactory extends Factory
{
    protected $model = ChatbotConversation::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'session_id' => fake()->uuid(),
            'locale' => fake()->randomElement(['fr', 'ar']),
            'started_at' => now()->subMinutes(fake()->numberBetween(5, 120)),
            'last_message_at' => now(),
            'is_reviewed' => false,
        ];
    }

    public function withClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => now(),
        ]);
    }
}
