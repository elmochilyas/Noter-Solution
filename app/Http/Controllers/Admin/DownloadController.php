<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Receipt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function receipt(Receipt $receipt): StreamedResponse
    {
        $this->authorize('view', $receipt);

        $disk = Storage::disk('receipts');
        if (! $disk->exists($receipt->storage_path)) {
            abort(404);
        }

        return $disk->download($receipt->storage_path, "recu-{$receipt->number}.pdf");
    }

    public function document(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        if ($document->scan_status !== 'clean') {
            abort(403, $document->scan_status === 'pending' ? __('portal.scanning') : __('portal.document_infected'));
        }

        if (! Storage::exists($document->storage_path)) {
            abort(404);
        }

        return Storage::download($document->storage_path, $document->original_filename);
    }
}
