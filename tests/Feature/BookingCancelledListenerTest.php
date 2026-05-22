<?php

use App\Domain\Services\PaymentService;
use App\Enums\BookingStatus;
use App\Events\BookingCancelled;
use App\Listeners\IssueRefundIfApplicable;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('IssueRefundIfApplicable creates refund request when cancelled 24h before', function () {
    $booking = Booking::factory()->create([
        'starts_at' => Carbon::parse('+36 hours'),
        'ends_at' => Carbon::parse('+37 hours'),
        'status' => BookingStatus::CANCELLED->value,
        'cancelled_at' => now(),
    ]);

    $payment = Payment::factory()->succeeded()->create([
        'booking_id' => $booking->id,
        'gateway' => 'stripe',
        'amount_centimes' => 50000,
    ]);

    $service = app(PaymentService::class);
    $listener = new IssueRefundIfApplicable($service);
    $listener->handle(new BookingCancelled($booking));

    $payment->refresh();
    expect(Refund::where('payment_id', $payment->id)->exists())->toBeTrue();
    expect(Refund::where('payment_id', $payment->id)->first()->amount_centimes)->toBe(50000);
});

test('IssueRefundIfApplicable creates 50% refund when cancelled 2-24h before', function () {
    $booking = Booking::factory()->create([
        'starts_at' => Carbon::parse('+4 hours'),
        'ends_at' => Carbon::parse('+5 hours'),
        'status' => BookingStatus::CANCELLED->value,
        'cancelled_at' => now(),
    ]);

    $payment = Payment::factory()->succeeded()->create([
        'booking_id' => $booking->id,
        'gateway' => 'stripe',
        'amount_centimes' => 50000,
    ]);

    $service = app(PaymentService::class);
    $listener = new IssueRefundIfApplicable($service);
    $listener->handle(new BookingCancelled($booking));

    $payment->refresh();
    expect(Refund::where('payment_id', $payment->id)->exists())->toBeTrue();
    expect(Refund::where('payment_id', $payment->id)->first()->amount_centimes)->toBe(25000);
});

test('IssueRefundIfApplicable creates no refund when cancelled less than 2h before', function () {
    $booking = Booking::factory()->create([
        'starts_at' => Carbon::parse('+30 minutes'),
        'ends_at' => Carbon::parse('+90 minutes'),
        'status' => BookingStatus::CANCELLED->value,
        'cancelled_at' => now(),
    ]);

    $payment = Payment::factory()->succeeded()->create([
        'booking_id' => $booking->id,
        'gateway' => 'stripe',
        'amount_centimes' => 50000,
    ]);

    $service = app(PaymentService::class);
    $listener = new IssueRefundIfApplicable($service);
    $listener->handle(new BookingCancelled($booking));

    $payment->refresh();
    expect($payment->refunds)->toBeEmpty();
});

test('IssueRefundIfApplicable does nothing for cash payments', function () {
    $booking = Booking::factory()->create([
        'starts_at' => Carbon::parse('+36 hours'),
        'ends_at' => Carbon::parse('+37 hours'),
        'status' => BookingStatus::CANCELLED->value,
        'cancelled_at' => now(),
    ]);

    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'gateway' => 'cash',
        'amount_centimes' => 50000,
        'status' => 'pending',
    ]);

    $service = app(PaymentService::class);
    $listener = new IssueRefundIfApplicable($service);
    $listener->handle(new BookingCancelled($booking));

    $payment->refresh();
    expect($payment->refunds)->toBeEmpty();
});
