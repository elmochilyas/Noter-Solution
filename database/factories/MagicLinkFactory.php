<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\MagicLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MagicLinkFactory extends Factory
{
    protected $model = MagicLink::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'consumed_at' => now(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }
}
