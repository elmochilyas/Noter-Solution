<x-mail::message>
# {{ __('notifications.hello', [], $locale) }} {{ $booking->client->full_name }},

{{ __('notifications.booking_confirmed.line1', [], $locale) }}

**{{ __('notifications.booking_confirmed.reference', [], $locale) }}:** {{ $booking->reference }}  
**{{ __('notifications.booking_confirmed.date', [], $locale) }}:** {{ $booking->starts_at->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}  
**{{ __('notifications.booking_confirmed.format', [], $locale) }}:** {{ $booking->format === 'online' ? __('booking.online', [], $locale) : __('booking.in_office', [], $locale) }}

{{ __('notifications.booking_confirmed.next_steps', [], $locale) }}

@if ($booking->format === 'online')
{{ __('notifications.booking_confirmed.video_note', [], $locale) }}
@else
{{ __('notifications.booking_confirmed.office_address', [], $locale) }}
@endif

<x-mail::button :url="route('portal.login', ['locale' => $locale])">
{{ __('notifications.booking_confirmed.portal_cta', [], $locale) }}
</x-mail::button>

{{ __('notifications.regards', [], $locale) }},<br>
{{ __('notifications.signature', [], $locale) }}
</x-mail::message>
