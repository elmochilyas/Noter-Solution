<?php

namespace Database\Factories;

use App\Models\AvailabilityException;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityExceptionFactory extends Factory
{
    protected $model = AvailabilityException::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 week', '+2 months');

        return [
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+1 day'),
            'reason' => fake()->sentence(),
            'is_holiday' => fake()->boolean(30),
        ];
    }
}
