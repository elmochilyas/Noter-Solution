<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(1),
            'title_translations' => ['fr' => fake()->sentence(3), 'ar' => fake()->sentence(3)],
            'intro_translations' => ['fr' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'body_translations' => ['fr' => fake()->paragraphs(3, true), 'ar' => fake()->paragraphs(3, true)],
            'transactions_translations' => ['fr' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'required_documents_translations' => ['fr' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'icon' => fake()->randomElement(['file-text', 'home', 'scale', 'handshake']),
            'display_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
