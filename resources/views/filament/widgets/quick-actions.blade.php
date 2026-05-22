<x-filament-widgets::widget>
    <x-filament::section heading="Actions rapides">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ \App\Filament\Resources\BookingResource::getUrl('index', ['tableFilters[status][value]' => 'confirmed']) }}"
               class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition hover:border-primary-500 hover:shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                    <x-filament::icon name="heroicon-o-calendar" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $todayBookings }}</p>
                    <p class="text-sm text-gray-500">Rendez-vous aujourd'hui</p>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\RefundResource::getUrl('index', ['tableFilters[status][value]' => 'requested']) }}"
               class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition hover:border-warning-500 hover:shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning-100 text-warning-600">
                    <x-filament::icon name="heroicon-o-arrow-uturn-left" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $pendingRefunds }}</p>
                    <p class="text-sm text-gray-500">Remboursements en attente</p>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\ContactMessageResource::getUrl('index') }}"
               class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition hover:border-danger-500 hover:shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-danger-100 text-danger-600">
                    <x-filament::icon name="heroicon-o-envelope" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $unhandledMessages }}</p>
                    <p class="text-sm text-gray-500">Messages non traités</p>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
