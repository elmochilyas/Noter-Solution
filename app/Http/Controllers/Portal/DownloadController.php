<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Services\DocumentService;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Receipt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function document(string $reference, Document $document, DocumentService $docs): StreamedResponse
    {
        $client = auth('client')->user();

        $booking = $client->bookings()->where('reference', $reference)->firstOrFail();

        if ($document->booking_id !== $booking->id) {
            abort(403);
        }

        if ($document->scan_status === 'infected') {
            abort(403, __('portal.document_infected'));
        }

        if (! Storage::exists($document->storage_path)) {
            abort(404);
        }

        return Storage::download($document->storage_path, $document->original_filename);
    }

    public function receipt(string $reference, Receipt $receipt): StreamedResponse
    {
        $client = auth('client')->user();

        $booking = $client->bookings()->where('reference', $reference)->firstOrFail();

        if ($receipt->booking_id !== $booking->id) {
            abort(403);
        }

        if (! Storage::disk('receipts')->exists($receipt->storage_path)) {
            abort(404);
        }

        return Storage::disk('receipts')->download($receipt->storage_path, "recu-{$receipt->number}.pdf");
    }
}
