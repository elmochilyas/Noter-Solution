<!DOCTYPE html>
<html dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>@lang('notifications.refund_issued.title', [], $locale) — {{ $creditNote->number }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc2626; padding-bottom: 10px; }
        .header h1 { color: #dc2626; font-size: 18px; margin: 0; }
        .header p { color: #666; margin: 4px 0; }
        .details { margin-bottom: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 4px 8px; }
        .details td:first-child { font-weight: bold; width: 160px; color: #555; }
        .amount-box { text-align: center; margin: 20px 0; padding: 15px; background: #fef2f2; border-radius: 8px; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #dc2626; }
        .amount-box .label { font-size: 11px; color: #666; }
        .reason { margin: 20px 0; padding: 12px; background: #f9fafb; border-left: 4px solid #dc2626; font-size: 11px; color: #555; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .footer p { margin: 2px 0; }
        @if($locale === 'ar')
        .details td:first-child { text-align: right; }
        .details td:last-child { text-align: left; }
        .reason { border-left: none; border-right: 4px solid #dc2626; }
        @endif
    </style>
</head>
<body>
    <div class="header">
        <h1>@lang('notifications.refund_issued.title', [], $locale)</h1>
        <p>@lang('notifications.payment_receipt.number', [], $locale): {{ $creditNote->number }}</p>
        <p>{{ $creditNote->issued_at?->locale($locale)->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <td>@lang('notifications.booking_confirmed.reference', [], $locale)</td>
                <td>{{ $booking?->reference }}</td>
            </tr>
            <tr>
                <td>@lang('booking.full_name')</td>
                <td>{{ $client?->full_name }}</td>
            </tr>
            @if($refund)
            <tr>
                <td>@lang('notifications.refund_issued.method', [], $locale)</td>
                <td>{{ $refund->gateway === 'stripe' ? __('notifications.refund_issued.card', [], $locale) : __('notifications.refund_issued.cash', [], $locale) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="amount-box">
        <div class="label">@lang('notifications.refund_issued.amount', [], $locale)</div>
        <div class="amount">{{ number_format($creditNote->amount_centimes / 100, 2, ',', ' ') }} {{ __('pdf.currency', [], $locale) }}</div>
    </div>

    @if($refund?->reason)
    <div class="reason">
        <strong>@lang('notifications.refund_issued.reason', [], $locale):</strong>
        <p style="margin: 4px 0 0;">{{ $refund->reason }}</p>
    </div>
    @endif

    <div class="footer">
        <p>@lang('notifications.signature', [], $locale)</p>
        <p>@lang('notifications.booking_confirmed.office_address', [], $locale)</p>
        <p>@lang('notifications.regards', [], $locale)</p>
    </div>
</body>
</html>
