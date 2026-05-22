<?php

use App\Domain\Services\ReceiptService;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generate creates a receipt with sequential number', function () {
    $payment = Payment::factory()->succeeded()->create([
        'amount_centimes' => 25000,
    ]);

    $service = new ReceiptService;
    $receipt = $service->generate($payment);

    expect($receipt->number)->toMatch('/^SBA-\d{4}-\d{6}$/');
    expect($receipt->booking_id)->toBe($payment->booking_id);
    expect($receipt->payment_id)->toBe($payment->id);
    expect($receipt->amount_centimes)->toBe(25000);
    expect($receipt->vat_centimes)->toBe(0);
    expect($receipt->storage_path)->toMatch('/^receipts\/\d{4}\/\d{2}\//');
});

test('temporaryUrl returns a URL', function () {
    $payment = Payment::factory()->succeeded()->create();

    $service = new ReceiptService;
    $receipt = $service->generate($payment);

    $url = $service->temporaryUrl($receipt);

    expect($url)->toBeString();
    expect($url)->not->toBeEmpty();
});

test('multiple receipts get different sequential numbers', function () {
    $payment1 = Payment::factory()->succeeded()->create();
    $payment2 = Payment::factory()->succeeded()->create();

    $service = new ReceiptService;

    $receipt1 = $service->generate($payment1);
    $receipt2 = $service->generate($payment2);

    expect($receipt1->number)->not->toBe($receipt2->number);
});
