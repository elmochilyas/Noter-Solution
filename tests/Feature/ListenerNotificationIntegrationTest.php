<?php

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Events\RefundIssued;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Refund;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmation;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\RefundIssuedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->client = Client::factory()->create();
});

test('BookingConfirmed triggers confirmation notification listener', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'confirmed',
    ]);

    BookingConfirmed::dispatch($booking);

    Notification::assertSentTo($this->client, BookingConfirmation::class);
});

test('BookingCancelled triggers cancellation notification listener', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'cancelled',
    ]);

    BookingCancelled::dispatch($booking);

    Notification::assertSentTo($this->client, BookingCancelledNotification::class);
});

test('PaymentSucceeded triggers receipt generation', function () {
    $booking = Booking::factory()->create(['client_id' => $this->client->id]);
    $payment = Payment::factory()->create(['booking_id' => $booking->id]);

    PaymentSucceeded::dispatch($payment);

    $booking->refresh();
    expect($booking->receipt)->not->toBeNull();
});

test('PaymentFailed triggers notification listener', function () {
    $booking = Booking::factory()->create(['client_id' => $this->client->id]);
    $payment = Payment::factory()->create(['booking_id' => $booking->id]);

    PaymentFailed::dispatch($payment);

    Notification::assertSentTo($this->client, PaymentFailedNotification::class);
});

test('RefundIssued triggers refund notification listener', function () {
    $booking = Booking::factory()->create(['client_id' => $this->client->id]);
    $payment = Payment::factory()->create(['booking_id' => $booking->id]);
    $refund = Refund::factory()->create(['payment_id' => $payment->id]);

    RefundIssued::dispatch($refund);

    Notification::assertSentTo($this->client, RefundIssuedNotification::class);
});
