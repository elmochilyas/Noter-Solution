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
        .header .bilingual { display: flex; justify-content: space-between; align-items: center; }
        .header .bilingual .fr { text-align: left; }
        .header .bilingual .ar { text-align: right; direction: rtl; }
        .header p { color: #666; margin: 4px 0; }
        .practice-info { font-size: 10px; color: #555; margin-top: 6px; }
        .practice-info span { display: inline-block; margin: 0 8px; }
        .details { margin-bottom: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 4px 8px; }
        .details td:first-child { font-weight: bold; width: 180px; color: #555; }
        .amount-box { text-align: center; margin: 20px 0; padding: 15px; background: #fef3c7; border-radius: 8px; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #d97706; }
        .amount-box .label { font-size: 11px; color: #666; }
        .table-header { background: #fef3c7; font-weight: bold; }
        .table-header td { color: #333; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .footer p { margin: 2px 0; }
        .vat-note { font-size: 10px; color: #888; margin-top: 8px; text-align: center; font-style: italic; }
        @if($locale === 'ar')
        .details td:first-child { text-align: right; }
        .details td:last-child { text-align: left; }
        @endif
    </style>
</head>
<body>
    @php
        $info = \App\Models\Setting::practiceInfo();
        $isFacture = $booking?->client?->ice ? true : false;
    @endphp

    <div class="header">
        <div class="bilingual">
            <div class="fr">
                <h1>Maître Sana Bouhamidi</h1>
                <p>Adoul à Agadir</p>
            </div>
            <div class="ar">
                <h1>الأستاذة سناء بوحميدي</h1>
                <p>عدل بأكادير</p>
            </div>
        </div>
        <p class="practice-info">
            ICE: {{ $info['ice'] ?: '…' }} &nbsp;|&nbsp; IF: {{ $info['if'] ?: '…' }} &nbsp;|&nbsp; RC: {{ $info['rc'] ?: '…' }} &nbsp;|&nbsp; Patente: {{ $info['patente'] ?: '…' }}
        </p>
        <p>{{ $info['address'] ?: __('notifications.booking_confirmed.office_address', [], $locale) }}</p>
        <p>{{ $info['phone'] }} &nbsp;|&nbsp; {{ $info['email'] }}</p>

        <h2 style="margin-top: 14px; font-size: 15px; color: #d97706;">
            {{ $isFacture ? 'FACTURE / فاتورة' : 'REÇU / إيصال' }} N° {{ $receipt->number }}
        </h2>
        <p>{{ __('notifications.payment_receipt.issued_at', [], $locale) }} {{ $receipt->issued_at->locale($locale)->isoFormat('dddd D MMMM YYYY') }}</p>
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
            @if($client?->national_id)
            <tr>
                <td>CIN</td>
                <td>{{ $client->national_id }}</td>
            </tr>
            @endif
            @if($client?->ice)
            <tr>
                <td>ICE (client)</td>
                <td>{{ $client->ice }}</td>
            </tr>
            @endif
            <tr>
                <td>{{ __('notifications.booking_confirmed.date', [], $locale) }}</td>
                <td>{{ $booking?->starts_at?->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</td>
            </tr>
            <tr>
                <td>{{ __('booking.your_request') }}</td>
                <td>{{ $booking?->description }}</td>
            </tr>
        </table>
    </div>

    @php
        $ht = $receipt->amount_centimes;
        $vat = $receipt->vat_centimes;
        $ttc = $ht + $vat;
        $vatRate = $ht > 0 ? round(($vat / $ht) * 100) : 0;
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr class="table-header">
            <td style="padding: 6px 8px; border: 1px solid #ddd; width: 60%;">{{ __('notifications.payment_receipt.description', [], $locale) }}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; width: 20%; text-align: right;">{{ __('notifications.payment_receipt.qty', [], $locale) }}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; width: 20%; text-align: right;">{{ __('notifications.payment_receipt.amount', [], $locale) }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #ddd;">
                {{ $client?->full_name }} — {{ $booking?->description }}
            </td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right;">1</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right;">{{ number_format($ht / 100, 2, ',', ' ') }}</td>
        </tr>
    </table>

    <div class="details">
        <table>
            <tr>
                <td>{{ __('booking.total') }} HT</td>
                <td>{{ number_format($ht / 100, 2, ',', ' ') }} {{ __('pdf.currency', [], $locale) }}</td>
            </tr>
            @if($vat > 0)
            <tr>
                <td>TVA ({{ $vatRate }}%)</td>
                <td>{{ number_format($vat / 100, 2, ',', ' ') }} {{ __('pdf.currency', [], $locale) }}</td>
            </tr>
            @endif
            <tr style="font-weight: bold; font-size: 14px;">
                <td>{{ __('booking.total') }} TTC</td>
                <td>{{ number_format($ttc / 100, 2, ',', ' ') }} {{ __('pdf.currency', [], $locale) }}</td>
            </tr>
        </table>
    </div>

    @if($vat === 0)
    <p class="vat-note">{{ __('notifications.payment_receipt.vat_exempt_note', [], $locale) }}</p>
    @endif

    <div class="amount-box">
        <div class="label">{{ __('notifications.payment_receipt.payment_method', [], $locale) }}</div>
        <div style="margin-top: 4px; font-size: 14px; color: #333;">
            @if($receipt->payment?->gateway === 'cash')
                {{ __('booking.cash_payment') }}
            @else
                {{ __('booking.card_payment') }} @if($receipt->payment?->gateway_reference)({{ $receipt->payment->gateway_reference }})@endif
            @endif
        </div>
        <div style="margin-top: 2px; font-size: 11px; color: #666;">
            {{ __('notifications.payment_receipt.payment_date', [], $locale) }} {{ $receipt->payment?->paid_at?->locale($locale)->isoFormat('dddd D MMMM YYYY') ?? $receipt->issued_at->locale($locale)->isoFormat('dddd D MMMM YYYY') }}
        </div>
    </div>

    <div class="footer">
        <p>{{ __('notifications.signature', [], $locale) }}</p>
        <p>{{ $info['address'] ?: __('notifications.booking_confirmed.office_address', [], $locale) }}</p>
        <p>{{ $info['phone'] }} &nbsp;|&nbsp; {{ $info['email'] }}</p>
        <p style="margin-top: 6px;">{{ __('notifications.regards', [], $locale) }}</p>
    </div>
</body>
</html>
