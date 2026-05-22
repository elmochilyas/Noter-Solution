<?php

use App\Domain\Payment\CreateIntentRequest;
use App\Domain\Payment\GatewayIntent;
use App\Domain\Payment\GatewayRefund;
use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Payment\PaymentGateway;
use App\Domain\Services\PaymentService;
use App\Enums\BookingStatus;
use App\Enums\PaymentGatewayName;
use App\Enums\PaymentStatus;
use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Events\RefundIssued;
use App\Models\Booking;
use App\Models\ConsultationPlan;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\ValueObjects\MoneyMad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->gateway = Mockery::mock(PaymentGateway::class);

    $this->service = new PaymentService($this->gateway);
});

afterEach(function () {
    Mockery::close();
});

test('createIntent creates a payment intent and stores a Payment record', function () {
    $plan = ConsultationPlan::factory()->create(['price_centimes' => 25000]);
    $booking = Booking::factory()->create([
        'consultation_plan_id' => $plan->id,
        'total_centimes' => 25000,
        'currency' => 'MAD',
        'status' => BookingStatus::PENDING_PAYMENT->value,
    ]);

    $this->gateway->shouldReceive('name')->once()->andReturn(PaymentGatewayName::STRIPE);
    $this->gateway->shouldReceive('createIntent')->once()->with(Mockery::type(CreateIntentRequest::class))
        ->andReturn(new GatewayIntent('pi_mock', 'secret_mock', 25000));

    $result = $this->service->createIntent($booking);

    expect($result['client_secret'])->toBe('secret_mock');
    expect($result['intent_id'])->toBe('pi_mock');

    $payment = Payment::where('booking_id', $booking->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->gateway)->toBe('stripe');
    expect($payment->gateway_intent_id)->toBe('pi_mock');
    expect($payment->amount_centimes)->toBe(25000);
    expect($payment->status)->toBe(PaymentStatus::PENDING->value);
});

test('confirmFromWebhook marks payment as succeeded and dispatches PaymentSucceeded', function () {
    Event::fake();

    $payment = Payment::factory()->create([
        'gateway_intent_id' => 'pi_success',
        'status' => PaymentStatus::PENDING->value,
    ]);

    $webhookEvent = new GatewayWebhookEvent(
        type: 'payment_intent.succeeded',
        id: 'evt_123',
        data: [
            'id' => 'pi_success',
            'amount' => 25000,
            'charges' => ['data' => [['id' => 'ch_abc']]],
        ],
    );

    $this->service->confirmFromWebhook($webhookEvent);

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::SUCCEEDED->value);
    expect($payment->gateway_charge_id)->toBe('ch_abc');

    Event::assertDispatched(PaymentSucceeded::class);
});

test('confirmFromWebhook marks payment as failed and dispatches PaymentFailed', function () {
    Event::fake();

    $payment = Payment::factory()->create([
        'gateway_intent_id' => 'pi_fail',
        'status' => PaymentStatus::PENDING->value,
    ]);

    $webhookEvent = new GatewayWebhookEvent(
        type: 'payment_intent.payment_failed',
        id: 'evt_456',
        data: ['id' => 'pi_fail', 'amount' => 25000],
    );

    $this->service->confirmFromWebhook($webhookEvent);

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::FAILED->value);

    Event::assertDispatched(PaymentFailed::class);
});

test('refund creates a refund request record', function () {
    $payment = Payment::factory()->create(['amount_centimes' => 25000]);
    $user = User::factory()->create();

    $refund = $this->service->refund(
        $payment,
        new MoneyMad(25000),
        'Client cancellation',
        $user,
    );

    expect($refund->payment_id)->toBe($payment->id);
    expect($refund->amount_centimes)->toBe(25000);
    expect($refund->reason)->toBe('Client cancellation');
    expect($refund->requested_by)->toBe($user->id);
    expect($refund->status)->toBe('requested');
    expect($refund->gateway_refund_id)->toBe('');
});

test('processRefund executes refund through gateway and dispatches RefundIssued', function () {
    Event::fake();

    $payment = Payment::factory()->succeeded()->create([
        'gateway_charge_id' => 'ch_test_123',
    ]);

    $reason = 'Client cancellation';

    $refund = Refund::factory()->create([
        'payment_id' => $payment->id,
        'amount_centimes' => 25000,
        'gateway_refund_id' => '',
        'reason' => $reason,
        'status' => 'requested',
    ]);

    $this->gateway->shouldReceive('refund')->once()
        ->with('ch_test_123', Mockery::type(MoneyMad::class), $reason)
        ->andReturn(new GatewayRefund('re_mock', 'ch_test_123', 25000));

    $this->service->processRefund($refund);

    $refund->refresh();
    expect($refund->gateway_refund_id)->toBe('re_mock');
    expect($refund->status)->toBe('succeeded');

    Event::assertDispatched(RefundIssued::class);
});

test('markCashSucceeded marks payment and dispatches PaymentSucceeded', function () {
    Event::fake();

    $payment = Payment::factory()->create([
        'gateway' => 'cash',
        'status' => PaymentStatus::PENDING->value,
    ]);
    $user = User::factory()->create();

    $this->service->markCashSucceeded($payment, $user);

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::SUCCEEDED->value);

    Event::assertDispatched(PaymentSucceeded::class);
});

test('createCashPending creates a cash payment', function () {
    $plan = ConsultationPlan::factory()->create(['price_centimes' => 30000]);
    $booking = Booking::factory()->create([
        'consultation_plan_id' => $plan->id,
        'total_centimes' => 30000,
    ]);

    $payment = $this->service->createCashPending($booking);

    expect($payment->gateway)->toBe('cash');
    expect($payment->amount_centimes)->toBe(30000);
    expect($payment->status)->toBe(PaymentStatus::PENDING->value);
    expect($payment->booking_id)->toBe($booking->id);
});
