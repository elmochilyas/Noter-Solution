<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraphs(3, true),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'is_handled' => false,
        ];
    }
}
