<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['general', 'booking', 'payment', 'services']),
            'question_translations' => ['fr' => fake()->sentence().'?', 'ar' => fake()->sentence().'؟'],
            'answer_translations' => ['fr' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'is_published' => true,
            'display_order' => fake()->numberBetween(1, 50),
            'view_count' => fake()->numberBetween(0, 1000),
        ];
    }
}
