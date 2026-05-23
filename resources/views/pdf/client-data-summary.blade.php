<!DOCTYPE html>
<html lang="{{ $locale ?? 'fr' }}">
<head><meta charset="utf-8"><title>{{ __('pdf.data_summary.title', ['name' => $client->full_name], $locale ?? 'fr') }}</title></head>
<body style="font-family:sans-serif;max-width:800px;margin:auto;padding:20px;">
    <h1>{{ __('pdf.data_summary.heading', [], $locale ?? 'fr') }}</h1>
    <p>{{ __('pdf.data_summary.intro', [], $locale ?? 'fr') }}</p>

    <h2>{{ __('pdf.data_summary.personal_info', [], $locale ?? 'fr') }}</h2>
    <ul>
        <li>{{ __('pdf.data_summary.name', [], $locale ?? 'fr') }} : {{ $client->full_name }}</li>
        <li>{{ __('pdf.data_summary.email', [], $locale ?? 'fr') }} : {{ $client->email }}</li>
        <li>{{ __('pdf.data_summary.phone', [], $locale ?? 'fr') }} : {{ $client->phone ?? __('pdf.data_summary.not_provided', [], $locale ?? 'fr') }}</li>
        <li>{{ __('pdf.data_summary.language', [], $locale ?? 'fr') }} : {{ $client->preferred_locale }}</li>
        <li>{{ __('pdf.data_summary.last_login', [], $locale ?? 'fr') }} : {{ $client->last_login_at?->format('d/m/Y H:i') ?? __('pdf.data_summary.never', [], $locale ?? 'fr') }}</li>
    </ul>

    <h2>{{ __('pdf.data_summary.bookings', ['count' => $bookings->count()], $locale ?? 'fr') }}</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
        <thead><tr>
            <th>{{ __('pdf.data_summary.reference', [], $locale ?? 'fr') }}</th>
            <th>{{ __('pdf.data_summary.date', [], $locale ?? 'fr') }}</th>
            <th>{{ __('pdf.data_summary.plan', [], $locale ?? 'fr') }}</th>
            <th>{{ __('pdf.data_summary.format', [], $locale ?? 'fr') }}</th>
            <th>{{ __('pdf.data_summary.status', [], $locale ?? 'fr') }}</th>
            <th>{{ __('pdf.data_summary.amount', [], $locale ?? 'fr') }}</th>
        </tr></thead>
        <tbody>
        @foreach ($bookings as $booking)
            <tr>
                <td>{{ $booking->reference }}</td>
                <td>{{ $booking->starts_at->format('d/m Y H:i') }}</td>
                <td>{{ $booking->plan?->name ?? '-' }}</td>
                <td>{{ $booking->format }}</td>
                <td>{{ $booking->status }}</td>
                <td>{{ $booking->total_centimes ? number_format($booking->total_centimes/100,2,',',' ').' '.__('pdf.currency') : '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
