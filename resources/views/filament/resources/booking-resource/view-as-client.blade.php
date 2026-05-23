<div class="space-y-6 p-6">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.reference') }}</h3>
            <p class="text-lg font-bold">{{ $booking->reference }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.status_label') }}</h3>
            <x-filament::badge>{{ __("booking.status.{$booking->status}") }}</x-filament::badge>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.plan') }}</h3>
            <p>{{ locale_string($booking->plan?->name_translations ?? [], app()->getLocale()) }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.date') }}</h3>
            <p>{{ $booking->starts_at?->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.format_label') }}</h3>
            <p>{{ __("booking.format.{$booking->format}") }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.amount') }}</h3>
            <p>{{ $booking->payment?->amount_centimes ? number_format($booking->payment->amount_centimes / 100, 2, ',', ' ').' '.__('plans.currency') : '-' }}</p>
        </div>
    </div>

    @if ($booking->description)
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('booking.description_label') }}</h3>
            <p class="mt-1">{{ $booking->description }}</p>
        </div>
    @endif

    <div>
        <h3 class="text-sm font-medium text-gray-500">{{ __('booking.documents') }}</h3>
        @if ($booking->documents->isEmpty())
            <p class="text-gray-400 italic">{{ __('booking.no_documents') }}</p>
        @else
            <ul class="list-disc list-inside">
                @foreach ($booking->documents as $doc)
                    <li>{{ $doc->original_filename }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
