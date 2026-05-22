<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'amount_centimes' => fake()->randomElement([5000, 10000, 15000]),
            'reason' => fake()->sentence(),
            'gateway_refund_id' => 're_'.fake()->lexify('????????????????'),
            'requested_by' => User::factory(),
            'approved_by' => User::factory(),
            'status' => 'pending',
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}
