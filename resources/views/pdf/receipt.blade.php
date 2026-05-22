<!DOCTYPE html>
<html dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('notifications.payment_receipt.number', [], $locale) }} {{ $receipt->number }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #d97706; padding-bottom: 10px; }
        .header h1 { color: #d97706; font-size: 18px; margin: 0; }
        .header p { color: #666; margin: 4px 0; }
        .details { margin-bottom: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 4px 8px; }
        .details td:first-child { font-weight: bold; width: 160px; color: #555; }
        .amount-box { text-align: center; margin: 20px 0; padding: 15px; background: #fef3c7; border-radius: 8px; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #d97706; }
        .amount-box .label { font-size: 11px; color: #666; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .footer p { margin: 2px 0; }
        @if($locale === 'ar')
        .details td:first-child { text-align: right; }
        .details td:last-child { text-align: left; }
        @endif
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('notifications.signature', [], $locale) }}</h1>
        <p>{{ __('notifications.payment_receipt.number', [], $locale) }}: {{ $receipt->number }}</p>
        <p>{{ $receipt->issued_at->locale($locale)->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <td>{{ __('notifications.booking_confirmed.reference', [], $locale) }}</td>
                <td>{{ $booking?->reference }}</td>
            </tr>
            <tr>
                <td>{{ __('booking.full_name') }}</td>
                <td>{{ $client?->full_name }}</td>
            </tr>
            <tr>
                <td>{{ __('notifications.booking_confirmed.date', [], $locale) }}</td>
                <td>{{ $booking?->starts_at?->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</td>
            </tr>
        </table>
    </div>

    <div class="amount-box">
        <div class="label">{{ __('notifications.payment_receipt.amount', [], $locale) }}</div>
        <div class="amount">{{ number_format($receipt->amount_centimes / 100, 2, ',', ' ') }} MAD</div>
        <div style="margin-top: 4px; font-size: 11px; color: #666;">
            {{ $receipt->payment?->gateway === 'cash' ? __('booking.cash_payment') : __('booking.card_payment') }}
        </div>
    </div>

    @if($receipt->vat_centimes > 0)
    <div class="details">
        <table>
            <tr>
                <td>TVA</td>
                <td>{{ number_format($receipt->vat_centimes / 100, 2, ',', ' ') }} MAD</td>
            </tr>
            <tr>
                <td>{{ __('booking.total') }} TTC</td>
                <td>{{ number_format(($receipt->amount_centimes + $receipt->vat_centimes) / 100, 2, ',', ' ') }} MAD</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>{{ __('notifications.signature', [], $locale) }}</p>
        <p>{{ __('notifications.booking_confirmed.office_address', [], $locale) }}</p>
        <p>{{ __('notifications.regards', [], $locale) }}</p>
    </div>
</body>
</html>
