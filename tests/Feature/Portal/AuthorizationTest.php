<?php

use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->clientA = Client::factory()->create();
    $this->clientB = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 50000]);
});

test('client A cannot see client B booking detail', function () {
    $bookingB = Booking::factory()->create(['client_id' => $this->clientB->id, 'consultation_plan_id' => $this->plan->id]);

    $this->actingAs($this->clientA, 'client')
        ->get("/fr/portal/bookings/{$bookingB->reference}")
        ->assertStatus(404);
});

test('client A cannot cancel client B booking', function () {
    $bookingB = Booking::factory()->create([
        'client_id' => $this->clientB->id,
        'consultation_plan_id' => $this->plan->id,
        'starts_at' => now()->addDay(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->clientA, 'client')
        ->post("/fr/portal/bookings/{$bookingB->reference}/cancel")
        ->assertStatus(404);
});

test('client A cannot download client B document', function () {
    $bookingB = Booking::factory()->create(['client_id' => $this->clientB->id, 'consultation_plan_id' => $this->plan->id]);
    $doc = Document::factory()->create(['booking_id' => $bookingB->id, 'client_id' => $this->clientB->id]);

    $this->actingAs($this->clientA, 'client')
        ->get("/fr/portal/bookings/{$bookingB->reference}/documents/{$doc->id}")
        ->assertStatus(404);
});

test('client A cannot download client B receipt', function () {
    $bookingB = Booking::factory()->create(['client_id' => $this->clientB->id, 'consultation_plan_id' => $this->plan->id]);
    $payment = Payment::factory()->create(['booking_id' => $bookingB->id]);
    $receipt = Receipt::factory()->create(['booking_id' => $bookingB->id, 'payment_id' => $payment->id]);

    $this->actingAs($this->clientA, 'client')
        ->get("/fr/portal/bookings/{$bookingB->reference}/receipt/{$receipt->id}")
        ->assertStatus(404);
});

test('client cannot download unscanned document', function () {
    $booking = Booking::factory()->create(['client_id' => $this->clientA->id, 'consultation_plan_id' => $this->plan->id]);
    $doc = Document::factory()->create([
        'booking_id' => $booking->id,
        'client_id' => $this->clientA->id,
        'scan_status' => 'pending',
        'storage_path' => 'test-doc-pending.txt',
    ]);

    Storage::fake('local');
    Storage::put('test-doc-pending.txt', 'fake content');

    $this->actingAs($this->clientA, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/documents/{$doc->id}")
        ->assertStatus(403);
});

test('client can download clean scanned document', function () {
    $booking = Booking::factory()->create(['client_id' => $this->clientA->id, 'consultation_plan_id' => $this->plan->id]);
    $doc = Document::factory()->create([
        'booking_id' => $booking->id,
        'client_id' => $this->clientA->id,
        'scan_status' => 'clean',
        'storage_path' => 'test-doc.txt',
    ]);

    Storage::fake('local');
    Storage::put('test-doc.txt', 'fake content');

    $this->actingAs($this->clientA, 'client')
        ->get("/fr/portal/bookings/{$booking->reference}/documents/{$doc->id}")
        ->assertStatus(200);
});
