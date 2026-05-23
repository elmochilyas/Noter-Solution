<?php

namespace App\Domain\Services;

use App\Jobs\ScanDocumentForViruses;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DocumentService
{
    public function attachToBooking(Booking $booking, UploadedFile $file, Client $client): Document
    {
        $uuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $storagePath = sprintf(
            'documents/%s/%s/%s.%s',
            now()->format('Y'),
            now()->format('m'),
            $uuid,
            $extension,
        );

        Storage::disk(config('filesystems.default'))
            ->put($storagePath, file_get_contents($file->getRealPath()));

        $document = Document::create([
            'uuid' => $uuid,
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'storage_path' => $storagePath,
            'scan_status' => 'pending',
            'purge_after' => now()->addDays(90),
        ]);

        ScanDocumentForViruses::dispatch($document);

        return $document;
    }

    public function temporaryUrl(Document $document, int $minutes = 5): string
    {
        $path = sprintf('/documents/%s/download', $document->uuid);

        return url($path).'?token='.now()->addMinutes($minutes)->timestamp;
    }

    public function delete(Document $document, User|Client $by): void
    {
        $document->delete();
    }
}
