<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'booking_id' => Booking::factory(),
            'gateway' => fake()->randomElement(['stripe', 'cmi', 'cash']),
            'gateway_intent_id' => 'pi_'.fake()->lexify('????????????????'),
            'amount_centimes' => fake()->randomElement([15000, 30000, 50000]),
            'currency' => 'MAD',
            'status' => 'pending',
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
