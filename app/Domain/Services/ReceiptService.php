<?php

namespace App\Domain\Services;

use App\Models\Payment;
use App\Models\Receipt;

final class ReceiptService
{
    public function generate(Payment $payment): Receipt
    {
        $seq = $this->nextSequenceNumber();
        $number = sprintf('SBA-%d-%06d', now()->year, $seq);

        $receipt = Receipt::create([
            'number' => $number,
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'amount_centimes' => $payment->amount_centimes,
            'vat_centimes' => 0,
            'storage_path' => sprintf('receipts/%s/%s/%s.pdf', now()->format('Y'), now()->format('m'), $number),
            'issued_at' => now(),
        ]);

        return $receipt;
    }

    public function temporaryUrl(Receipt $receipt, int $minutes = 5): string
    {
        $path = sprintf('/receipts/%s/download', $receipt->number);

        return url($path).'?token='.now()->addMinutes($minutes)->timestamp;
    }

    private function nextSequenceNumber(): int
    {
        try {
            $result = \DB::selectOne("SELECT nextval('receipts_number_seq') AS seq");

            return $result->seq;
        } catch (\Exception) {
            $max = \DB::table('receipts')->max('id') ?? 0;

            return $max + 1;
        }
    }
}
