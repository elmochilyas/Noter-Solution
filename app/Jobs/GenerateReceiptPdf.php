<?php

namespace App\Jobs;

use App\Domain\Services\NotificationService;
use App\Models\Receipt;
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
            $html = view('pdf.receipt', [
                'receipt' => $this->receipt,
                'locale' => $this->receipt->booking?->client?->preferred_locale ?? 'fr',
                'booking' => $this->receipt->booking,
                'client' => $this->receipt->booking?->client,
            ])->render();

            $storagePath = $this->receipt->storage_path;

            if ($storagePath) {
                $disk = Storage::disk('receipts');
                $disk->put($storagePath, $html);
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
