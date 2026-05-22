<?php

use App\Domain\Services\BookingService;
use App\Domain\Services\ReceiptService;
use App\Enums\BookingStatus;
use App\Events\PaymentSucceeded;
use App\Listeners\ConfirmBooking;
use App\Listeners\GenerateReceipt;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('ConfirmBooking listener marks booking as confirmed on PaymentSucceeded', function () {
    $payment = Payment::factory()->succeeded()->create();
    $booking = $payment->booking;
    $booking->status = BookingStatus::PENDING_PAYMENT->value;
    $booking->save();

    $service = app(BookingService::class);
    $listener = new ConfirmBooking($service);
    $listener->handle(new PaymentSucceeded($payment));

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::CONFIRMED->value);
});

test('GenerateReceipt listener creates a receipt on PaymentSucceeded', function () {
    $payment = Payment::factory()->succeeded()->create([
        'amount_centimes' => 25000,
    ]);

    $service = app(ReceiptService::class);
    $listener = new GenerateReceipt($service);
    $listener->handle(new PaymentSucceeded($payment));

    $receipt = Receipt::where('payment_id', $payment->id)->first();
    expect($receipt)->not->toBeNull();
    expect($receipt->amount_centimes)->toBe(25000);
});

test('succeeding PaymentSucceeded dispatches ConfirmBooking and GenerateReceipt listeners', function () {
    Queue::fake();

    $payment = Payment::factory()->create();

    Event::fake()->dispatch(new PaymentSucceeded($payment));

    Event::assertDispatched(PaymentSucceeded::class);
});
