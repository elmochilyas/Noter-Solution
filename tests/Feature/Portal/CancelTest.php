<?php

use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 50000]);
});

test('client can see cancel confirmation page', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDay(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/cancel")
        ->assertStatus(200)
        ->assertSee(__('portal.cancel_title'));
});

test('client cannot cancel booking less than 2 hours before', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addHour(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/cancel")
        ->assertStatus(403);
});

test('client can cancel booking successfully', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDay(),
        'status' => 'confirmed',
        'total_centimes' => 50000,
    ]);

    $this->actingAs($this->client, 'client')
        ->post("/fr/portal/bookings/{$booking->reference}/cancel", ['reason' => 'J\'ai un empêchement'])
        ->assertRedirect('/fr/portal/dashboard');

    $booking->refresh();
    expect($booking->status)->toBe('cancelled');
    expect($booking->cancellation_reason)->toBe('J\'ai un empêchement');
});

test('client cannot cancel terminal booking', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDay(),
        'status' => 'completed',
    ]);

    $this->actingAs($this->client, 'client')
        ->post("/fr/portal/bookings/{$booking->reference}/cancel")
        ->assertStatus(403);
});
