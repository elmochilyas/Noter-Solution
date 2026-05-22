<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+2126'.fake()->numerify('########'),
            'full_name' => fake()->name(),
            'preferred_locale' => fake()->randomElement(['fr', 'ar']),
            'preferred_channel' => fake()->randomElement(['email', 'sms', 'whatsapp']),
            'national_id' => 'BE'.fake()->numerify('#######'),
            'national_id_last4' => fake()->numerify('####'),
        ];
    }
}
