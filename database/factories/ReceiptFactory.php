<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        return [
            'number' => 'R-'.fake()->year().'-'.fake()->numerify('######'),
            'booking_id' => Booking::factory(),
            'payment_id' => Payment::factory(),
            'amount_centimes' => fake()->randomElement([15000, 30000, 50000]),
            'vat_centimes' => fake()->randomElement([0, 3000, 6000]),
            'storage_path' => 'receipts/'.fake()->uuid().'.pdf',
            'issued_at' => now(),
        ];
    }
}
