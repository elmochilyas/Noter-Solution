<?php

use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 50000]);
});

test('client can view receipts list', function () {
    $booking = Booking::factory()->create(['client_id' => $this->client->id, 'consultation_plan_id' => $this->plan->id]);
    $payment = Payment::factory()->create(['booking_id' => $booking->id]);
    Receipt::factory()->create(['booking_id' => $booking->id, 'payment_id' => $payment->id]);

    $this->actingAs($this->client, 'client')
        ->get('/fr/portal/receipts')
        ->assertStatus(200)
        ->assertSee(__('portal.receipts_title'));
});

test('guest cannot access receipts', function () {
    $this->get('/fr/portal/receipts')->assertRedirect();
});
