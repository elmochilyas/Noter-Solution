<?php

namespace Database\Factories;

use App\Models\NotificationsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationsLogFactory extends Factory
{
    protected $model = NotificationsLog::class;

    public function definition(): array
    {
        return [
            'recipient_type' => fake()->randomElement(['client', 'user']),
            'recipient_id' => fake()->numberBetween(1, 100),
            'channel' => fake()->randomElement(['email', 'sms', 'whatsapp']),
            'template_key' => fake()->randomElement(['booking.confirmed', 'booking.cancelled', 'magic.link']),
            'status' => fake()->randomElement(['sent', 'delivered', 'failed']),
            'sent_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => fake()->sentence(),
        ]);
    }
}
