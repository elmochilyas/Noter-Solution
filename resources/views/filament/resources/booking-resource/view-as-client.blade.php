<div class="space-y-6 p-6">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Référence') }}</h3>
            <p class="text-lg font-bold">{{ $booking->reference }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Statut') }}</h3>
            <x-filament::badge>{{ __("booking.status.{$booking->status}") }}</x-filament::badge>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Formule') }}</h3>
            <p>{{ $booking->plan?->name_translations[app()->getLocale()] ?? $booking->plan?->name_translations['fr'] }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Date') }}</h3>
            <p>{{ $booking->starts_at?->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Format') }}</h3>
            <p>{{ $booking->format === 'online' ? 'En ligne' : 'Au cabinet' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Montant') }}</h3>
            <p>{{ $booking->payment?->amount_centimes ? number_format($booking->payment->amount_centimes / 100, 2, ',', ' ').' MAD' : '-' }}</p>
        </div>
    </div>

    @if ($booking->description)
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Description') }}</h3>
            <p class="mt-1">{{ $booking->description }}</p>
        </div>
    @endif

    <div>
        <h3 class="text-sm font-medium text-gray-500">{{ __('Documents') }}</h3>
        @if ($booking->documents->isEmpty())
            <p class="text-gray-400 italic">{{ __('Aucun document') }}</p>
        @else
            <ul class="list-disc list-inside">
                @foreach ($booking->documents as $doc)
                    <li>{{ $doc->original_filename }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
