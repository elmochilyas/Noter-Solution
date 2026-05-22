<?php

namespace App\Listeners;

use App\Events\RefundIssued;
use App\Models\CreditNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class GenerateCreditNote implements ShouldQueue
{
    public function handle(RefundIssued $event): void
    {
        $refund = $event->refund;
        $payment = $refund->payment;
        $booking = $payment->booking;

        if (! $booking) {
            return;
        }

        $seq = $this->nextSequenceNumber();
        $number = sprintf('AV-%d-%06d', now()->year, $seq);

        $storagePath = sprintf('credit-notes/%s/%s/%s.pdf', now()->format('Y'), now()->format('m'), $number);

        try {
            CreditNote::create([
                'number' => $number,
                'refund_id' => $refund->id,
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'amount_centimes' => $refund->amount_centimes,
                'vat_centimes' => 0,
                'reason' => $refund->reason,
                'storage_path' => $storagePath,
                'issued_at' => now(),
            ]);

            Log::info('Credit note generated', [
                'number' => $number,
                'refund_id' => $refund->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate credit note', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function nextSequenceNumber(): int
    {
        try {
            $result = DB::selectOne("SELECT nextval('credit_notes_number_seq') AS seq");

            return $result->seq;
        } catch (\Exception) {
            $max = DB::table('credit_notes')->max('id') ?? 0;

            return $max + 1;
        }
    }
}
