<?php

namespace Database\Factories;

use App\Models\AvailabilityRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityRuleFactory extends Factory
{
    protected $model = AvailabilityRule::class;

    public function definition(): array
    {
        return [
            'day_of_week' => fake()->numberBetween(1, 5),
            'starts_at' => '09:00',
            'ends_at' => '17:00',
            'format' => fake()->randomElement(['online', 'in_office', 'both']),
            'is_active' => true,
        ];
    }
}
