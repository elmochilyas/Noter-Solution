<?php

use App\Domain\Services\DocumentService;
use App\Domain\Services\VirusScanner\Contracts\VirusScanner;
use App\Domain\Services\VirusScanner\ScanResult;
use App\Domain\Services\VirusScanner\ScanStatus;
use App\Enums\DocumentScanStatus;
use App\Infrastructure\VirusScanner\ClamavScanner;
use App\Infrastructure\VirusScanner\NullScanner;
use App\Jobs\ScanDocumentForViruses;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

describe('VirusScanner drivers', function () {
    it('NullScanner always returns clean', function () {
        $scanner = new NullScanner;

        $result = $scanner->scan('/nonexistent/file.pdf');

        expect($result)->toBeInstanceOf(ScanResult::class);
        expect($result->status)->toBe(ScanStatus::CLEAN);
        expect($scanner->name())->toBe('null');
    });

    it('ClamAV scanner handles connection failure gracefully', function () {
        $scanner = new ClamavScanner(
            host: '127.0.0.1',
            port: 13306,
            timeout: 1.0,
        );

        $result = $scanner->scan(__FILE__);

        expect($result)->toBeInstanceOf(ScanResult::class);
        expect($result->status)->toBe(ScanStatus::ERROR);
    });
});

describe('ScanDocumentForViruses job', function () {
    it('marks document clean when scanner returns clean', function () {
        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $file = UploadedFile::fake()->create('safe.pdf', 100, 'application/pdf');

        $storagePath = 'documents/test/safe.pdf';
        Storage::disk('local')->put($storagePath, file_get_contents($file->getRealPath()));

        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'storage_path' => $storagePath,
            'scan_status' => DocumentScanStatus::PENDING->value,
            'original_filename' => 'safe.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
        ]);

        $job = new ScanDocumentForViruses($document);
        $job->handle(app(VirusScanner::class));

        $document->refresh();
        expect($document->scan_status)->toBe(DocumentScanStatus::CLEAN->value);
        expect($document->scanned_at)->not->toBeNull();
    });

    it('deletes infected documents and updates status', function () {
        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $file = UploadedFile::fake()->create('evil.pdf', 100, 'application/pdf');

        $storagePath = 'documents/test/evil.pdf';
        Storage::disk('local')->put($storagePath, file_get_contents($file->getRealPath()));

        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'storage_path' => $storagePath,
            'scan_status' => DocumentScanStatus::PENDING->value,
            'original_filename' => 'evil.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
        ]);

        $mockScanner = mock(VirusScanner::class);
        $mockScanner->shouldReceive('scan')->andReturn(
            new ScanResult(ScanStatus::INFECTED, 'Eicar-Test-Signature FOUND')
        );

        $job = new ScanDocumentForViruses($document);
        $job->handle($mockScanner);

        $document->refresh();
        expect($document->scan_status)->toBe(DocumentScanStatus::INFECTED->value);
        expect($document->scanned_at)->not->toBeNull();
        Storage::disk('local')->assertMissing($storagePath);
    });

    it('handles scanner errors gracefully', function () {
        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $file = UploadedFile::fake()->create('unknown.pdf', 100, 'application/pdf');

        $storagePath = 'documents/test/unknown.pdf';
        Storage::disk('local')->put($storagePath, file_get_contents($file->getRealPath()));

        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'storage_path' => $storagePath,
            'scan_status' => DocumentScanStatus::PENDING->value,
        ]);

        $mockScanner = mock(VirusScanner::class);
        $mockScanner->shouldReceive('scan')->andReturn(
            new ScanResult(ScanStatus::ERROR, 'Daemon unavailable')
        );

        $job = new ScanDocumentForViruses($document);
        $job->handle($mockScanner);

        $document->refresh();
        expect($document->scan_status)->toBe(DocumentScanStatus::PENDING->value);
    });
});

describe('DocumentService dispatches scan job', function () {
    it('dispatches ScanDocumentForViruses on file upload', function () {
        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $service = app(DocumentService::class);
        $document = $service->attachToBooking($booking, $file, $client);

        Queue::assertPushed(ScanDocumentForViruses::class, function ($job) use ($document) {
            return $job->document->id === $document->id;
        });
    });
});

describe('Download controllers block infected files', function () {
    it('portal download blocks infected documents', function () {
        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'scan_status' => DocumentScanStatus::INFECTED->value,
            'storage_path' => 'documents/test/infected.pdf',
        ]);

        $this->actingAs($client, 'client')
            ->get(route('portal.bookings.documents.download', [
                'locale' => 'fr',
                'reference' => $booking->reference,
                'document' => $document->id,
            ]))
            ->assertForbidden();
    });

    it('admin download blocks infected documents', function () {
        Role::create(['name' => 'owner', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('owner');

        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'scan_status' => DocumentScanStatus::INFECTED->value,
            'storage_path' => 'documents/test/infected.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.downloads.document', [
                'document' => $document->id,
            ]))
            ->assertForbidden();
    });
});
