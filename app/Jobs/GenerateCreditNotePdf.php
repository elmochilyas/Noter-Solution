<?php

namespace App\Jobs;

use App\Models\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateCreditNotePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CreditNote $creditNote,
    ) {}

    public function handle(): void
    {
        try {
            $locale = $this->creditNote->booking?->client?->preferred_locale ?? 'fr';

            $pdf = Pdf::loadView('pdf.credit-note', [
                'creditNote' => $this->creditNote,
                'locale' => $locale,
                'booking' => $this->creditNote->booking,
                'client' => $this->creditNote->booking?->client,
                'refund' => $this->creditNote->refund,
            ]);

            $storagePath = $this->creditNote->storage_path;

            if ($storagePath) {
                $disk = Storage::disk('receipts');
                $disk->put($storagePath, $pdf->output());
            }

            Log::info('Credit note PDF generated', [
                'number' => $this->creditNote->number,
                'credit_note_id' => $this->creditNote->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate credit note PDF', [
                'credit_note_id' => $this->creditNote->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
