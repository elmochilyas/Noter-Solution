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

test('client can view bookings list', function () {
    Booking::factory()->count(3)->create(['client_id' => $this->client->id, 'consultation_plan_id' => $this->plan->id]);

    $this->actingAs($this->client, 'client')
        ->get('/fr/portal/bookings')
        ->assertStatus(200)
        ->assertSee(__('portal.bookings_title'));
});

test('client sees own bookings only', function () {
    $otherClient = Client::factory()->create();
    Booking::factory()->create(['client_id' => $otherClient->id, 'consultation_plan_id' => $this->plan->id]);

    $this->actingAs($this->client, 'client')
        ->get('/fr/portal/bookings')
        ->assertDontSee($otherClient->email);
});

test('client can view booking detail', function () {
    $booking = Booking::factory()->create(['client_id' => $this->client->id, 'consultation_plan_id' => $this->plan->id]);
    $booking->refresh();

    $this->actingAs($this->client, 'client');

    $response = $this->get("/fr/portal/bookings/{$booking->reference}");

    $response->assertStatus(200)
        ->assertSee($booking->reference);
});

test('client cannot view another clients booking', function () {
    $other = Client::factory()->create();
    $booking = Booking::factory()->create(['client_id' => $other->id, 'consultation_plan_id' => $this->plan->id]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}")
        ->assertStatus(404);
});

test('guest cannot access bookings', function () {
    $this->get('/fr/portal/bookings')->assertRedirect();
});

test('booking detail shows cancel button for eligible booking', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addHours(3),
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}")
        ->assertStatus(200)
        ->assertSee(__('portal.cancel_booking'));
});

test('booking detail hides cancel button for ineligible booking', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addHour(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->client, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}")
        ->assertStatus(200)
        ->assertDontSee(__('portal.cancel_booking'));
});
