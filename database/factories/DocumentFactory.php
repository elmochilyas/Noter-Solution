<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'booking_id' => Booking::factory(),
            'client_id' => Client::factory(),
            'original_filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(10000, 5000000),
            'storage_path' => 'documents/'.fake()->uuid().'.pdf',
            'scan_status' => 'pending',
        ];
    }

    public function scanned(): static
    {
        return $this->state(fn (array $attributes) => [
            'scan_status' => fake()->randomElement(['clean', 'infected']),
            'scanned_at' => now(),
        ]);
    }
}
