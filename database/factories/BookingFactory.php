<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $plan = ConsultationPlan::factory();

        return [
            'reference' => 'SBA-'.strtoupper(fake()->bothify('??????')),
            'client_id' => Client::factory(),
            'consultation_plan_id' => $plan,
            'service_category' => fake()->randomElement(['family', 'real_estate', 'financial', 'contracts']),
            'description' => fake()->sentence(),
            'format' => fake()->randomElement(['online', 'in_office']),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'ends_at' => fn (array $attrs) => Carbon::parse($attrs['starts_at'])->addHour(),
            'status' => BookingStatus::PENDING_PAYMENT->value,
            'total_centimes' => fake()->randomElement([0, 15000, 30000]),
            'currency' => 'MAD',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CONFIRMED->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
