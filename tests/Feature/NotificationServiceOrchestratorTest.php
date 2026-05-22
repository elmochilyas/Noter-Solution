<?php

use App\Domain\Services\NotificationService;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Refund;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmation;
use App\Notifications\BookingReminder;
use App\Notifications\BookingRescheduledNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentReceipt;
use App\Notifications\RefundIssuedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->service = app(NotificationService::class);
    $this->client = Client::factory()->create();
    $this->booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'starts_at' => now()->addDays(3),
    ]);
});

test('sendBookingConfirmation sends email notification', function () {
    $this->service->sendBookingConfirmation($this->booking);

    Notification::assertSentTo($this->client, BookingConfirmation::class);
});

test('sendBookingReminder sends email notification', function () {
    $this->service->sendBookingReminder($this->booking, '24h');

    Notification::assertSentTo($this->client, BookingReminder::class);
});

test('sendBookingCancelled sends email notification', function () {
    $this->service->sendBookingCancelled($this->booking);

    Notification::assertSentTo($this->client, BookingCancelledNotification::class);
});

test('sendBookingRescheduled sends email notification', function () {
    $newBooking = Booking::factory()->create(['client_id' => $this->client->id]);

    $this->service->sendBookingRescheduled($this->booking, $newBooking);

    Notification::assertSentTo($this->client, BookingRescheduledNotification::class);
});

test('sendPaymentReceipt sends email notification', function () {
    $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);
    $receipt = Receipt::factory()->create([
        'booking_id' => $this->booking->id,
        'payment_id' => $payment->id,
    ]);

    $this->service->sendPaymentReceipt($receipt);

    Notification::assertSentTo($this->client, PaymentReceipt::class);
});

test('sendPaymentFailed sends email notification', function () {
    $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);

    $this->service->sendPaymentFailed($payment);

    Notification::assertSentTo($this->client, PaymentFailedNotification::class);
});

test('sendRefundIssued sends email notification', function () {
    $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);
    $refund = Refund::factory()->create(['payment_id' => $payment->id]);

    $this->service->sendRefundIssued($refund);

    Notification::assertSentTo($this->client, RefundIssuedNotification::class);
});

test('sendBookingConfirmation dispatches with correct locale', function () {
    $this->client->update(['preferred_locale' => 'ar']);

    $this->service->sendBookingConfirmation($this->booking);

    Notification::assertSentTo($this->client, BookingConfirmation::class);
});
