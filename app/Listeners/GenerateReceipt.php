<?php

namespace App\Listeners;

use App\Domain\Services\ReceiptService;
use App\Events\PaymentSucceeded;
use App\Jobs\GenerateReceiptPdf;
use Illuminate\Contracts\Queue\ShouldQueue;

final class GenerateReceipt implements ShouldQueue
{
    public function __construct(
        private readonly ReceiptService $receipts,
    ) {}

    public function handle(PaymentSucceeded $event): void
    {
        $receipt = $this->receipts->generate($event->payment);

        GenerateReceiptPdf::dispatch($receipt);
    }
}
