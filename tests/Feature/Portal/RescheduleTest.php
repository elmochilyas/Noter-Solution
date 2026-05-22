<?php

use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 50000, 'duration_minutes' => 60]);

    AvailabilityRule::factory()->create([
        'day_of_week' => CarbonImmutable::now()->addDay()->dayOfWeekIso,
        'starts_at' => '09:00',
        'ends_at' => '17:00',
        'format' => 'both',
        'is_active' => true,
    ]);
});

test('client can see reschedule form', function () {
    $booking = Booking::factory()->confirmed()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDays(3),
        'format' => 'online',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/reschedule")
        ->assertStatus(200)
        ->assertSee(__('portal.reschedule_title'));
});

test('client cannot reschedule with less than 2 hours notice', function () {
    $booking = Booking::factory()->confirmed()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addHour(),
        'format' => 'online',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/reschedule")
        ->assertStatus(403);
});

test('client cannot reschedule non-confirmed booking', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDays(3),
        'format' => 'online',
        'status' => 'pending_payment',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/reschedule")
        ->assertStatus(403);
});

test('client can reschedule booking', function () {
    $nextSlot = CarbonImmutable::now()->addDays(3)->setHours(10)->setMinutes(0)->setSeconds(0);

    $booking = Booking::factory()->confirmed()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => $nextSlot->addDay(),
        'format' => 'online',
        'total_centimes' => 50000,
    ]);

    $this->actingAs($this->client, 'client')
        ->post("/fr/portal/bookings/{$booking->reference}/reschedule", [
            'starts_at' => $nextSlot->toIso8601String(),
        ])
        ->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe('cancelled');

    $newBooking = Booking::where('client_id', $this->client->id)
        ->where('status', 'pending_payment')
        ->first();
    expect($newBooking)->not->toBeNull();
    expect($newBooking->reference)->not->toBe($booking->reference);
});

test('guest cannot access reschedule', function () {
    $this->get('/fr/portal/bookings/SBA-TEST/reschedule')->assertRedirect();
});
