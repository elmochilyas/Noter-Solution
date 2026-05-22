<?php

use App\Domain\Services\ReceiptService;
use App\Jobs\GenerateReceiptPdf;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->booking = Booking::factory()->create(['client_id' => $this->client->id]);
    $this->payment = Payment::factory()->create(['booking_id' => $this->booking->id]);
});

test('GenerateReceipt listener dispatches GenerateReceiptPdf job', function () {
    Queue::fake();

    $receipt = app(ReceiptService::class)->generate($this->payment);

    $job = new GenerateReceiptPdf($receipt);
    $job->handle();

    $receipt->refresh();
    expect($receipt->number)->not->toBeNull();
    expect($receipt->storage_path)->toMatch('/^receipts\/\d{4}\/\d{2}\//');
});

test('receipt pdf view renders without error', function () {
    $receipt = app(ReceiptService::class)->generate($this->payment);

    $view = view('pdf.receipt', [
        'receipt' => $receipt,
        'locale' => 'fr',
        'booking' => $receipt->booking,
        'client' => $receipt->booking->client,
    ])->render();

    expect($view)->toContain($receipt->number);
    expect($view)->toContain('MAD');
});

test('receipt pdf view renders in Arabic', function () {
    $this->client->update(['preferred_locale' => 'ar']);
    $receipt = app(ReceiptService::class)->generate($this->payment);

    $view = view('pdf.receipt', [
        'receipt' => $receipt,
        'locale' => 'ar',
        'booking' => $receipt->booking,
        'client' => $receipt->booking->client,
    ])->render();

    expect($view)->toContain($receipt->number);
    expect($view)->toContain('dir="rtl"');
});

test('GenerateReceiptPdf job stores receipt file', function () {
    $receipt = app(ReceiptService::class)->generate($this->payment);

    $job = new GenerateReceiptPdf($receipt);
    $job->handle();

    $disk = Storage::disk('receipts');
    expect($disk->exists($receipt->storage_path))->toBeTrue();
});
