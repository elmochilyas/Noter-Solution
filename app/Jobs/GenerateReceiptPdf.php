<?php

namespace App\Jobs;

use App\Domain\Services\NotificationService;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReceiptPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Receipt $receipt,
    ) {}

    public function handle(): void
    {
        try {
            $locale = $this->receipt->booking?->client?->preferred_locale ?? 'fr';

            $pdf = Pdf::loadView('pdf.receipt', [
                'receipt' => $this->receipt,
                'locale' => $locale,
                'booking' => $this->receipt->booking,
                'client' => $this->receipt->booking?->client,
            ]);

            $storagePath = $this->receipt->storage_path;

            if ($storagePath) {
                $disk = Storage::disk('receipts');
                $disk->put($storagePath, $pdf->output());
            }

            app(NotificationService::class)->sendPaymentReceipt($this->receipt);
        } catch (\Throwable $e) {
            Log::error('Failed to generate receipt PDF', [
                'receipt_id' => $this->receipt->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
