<?php

namespace Database\Factories;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatbotMessageFactory extends Factory
{
    protected $model = ChatbotMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => ChatbotConversation::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'tokens_in' => fake()->numberBetween(10, 200),
            'tokens_out' => fake()->numberBetween(50, 500),
            'latency_ms' => fake()->numberBetween(200, 3000),
            'created_at' => now(),
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
        ]);
    }
}
