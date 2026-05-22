<?php

namespace Database\Factories;

use App\Models\ConsultationPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationPlanFactory extends Factory
{
    protected $model = ConsultationPlan::class;

    public function definition(): array
    {
        $formats = ['online', 'in_office', 'both'];

        return [
            'slug' => fake()->unique()->slug(1),
            'name_translations' => ['fr' => fake()->sentence(3), 'ar' => fake()->sentence(3)],
            'description_translations' => ['fr' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'included_features' => [fake()->sentence(), fake()->sentence(), fake()->sentence()],
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price_centimes' => fake()->randomElement([0, 15000, 30000, 50000]),
            'format' => fake()->randomElement($formats),
            'is_recommended' => false,
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_centimes' => 0,
        ]);
    }

    public function recommended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recommended' => true,
        ]);
    }
}
