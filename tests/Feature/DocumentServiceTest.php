<?php

use App\Domain\Services\DocumentService;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('attachToBooking stores file and creates Document record', function () {
    $client = Client::factory()->create();
    $booking = Booking::factory()->create(['client_id' => $client->id]);
    $file = UploadedFile::fake()->create('cin.pdf', 100, 'application/pdf');

    $service = new DocumentService;
    $document = $service->attachToBooking($booking, $file, $client);

    expect($document->booking_id)->toBe($booking->id);
    expect($document->client_id)->toBe($client->id);
    expect($document->original_filename)->toBe('cin.pdf');
    expect($document->mime_type)->toBe('application/pdf');
    expect($document->size_bytes)->toBeGreaterThan(0);
    expect($document->scan_status)->toBe('pending');

    Storage::disk('local')->assertExists($document->storage_path);
});

test('temporaryUrl returns a URL', function () {
    $client = Client::factory()->create();
    $booking = Booking::factory()->create(['client_id' => $client->id]);
    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');

    $service = new DocumentService;
    $document = $service->attachToBooking($booking, $file, $client);

    $url = $service->temporaryUrl($document);

    expect($url)->toBeString();
    expect($url)->not->toBeEmpty();
});

test('delete removes the document', function () {
    $client = Client::factory()->create();
    $booking = Booking::factory()->create(['client_id' => $client->id]);
    $file = UploadedFile::fake()->create('delete-me.pdf', 50, 'application/pdf');

    $service = new DocumentService;
    $document = $service->attachToBooking($booking, $file, $client);

    $service->delete($document, $client);

    expect(Document::find($document->id))->toBeNull();
});
