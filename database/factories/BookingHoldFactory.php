<?php

namespace Database\Factories;

use App\Models\BookingHold;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingHoldFactory extends Factory
{
    protected $model = BookingHold::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 week');

        return [
            'slot_starts_at' => $start,
            'slot_ends_at' => (clone $start)->modify('+1 hour'),
            'session_id' => fake()->uuid(),
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }
}
