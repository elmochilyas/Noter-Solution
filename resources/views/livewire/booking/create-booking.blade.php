<div class="min-h-screen bg-gray-50 py-4 sm:py-8" x-data="{ step: $wire.entangle('step') }">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        {{-- Progress indicator --}}
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center justify-between">
                @php $labels = [1 => __('booking.step_plan'), 2 => __('booking.step_details'), 3 => __('booking.step_slot'), 4 => __('booking.step_identity'), 5 => __('booking.step_documents'), 6 => __('booking.step_payment')]; @endphp
                @foreach ($labels as $num => $label)
                    <div class="flex flex-col items-center">
                        <div @class([
                            'flex h-8 w-8 min-w-[32px] items-center justify-center rounded-full text-sm font-medium',
                            'bg-amber-600 text-white' => $num === $step,
                            'bg-amber-200 text-amber-800' => $num < $step,
                            'bg-gray-200 text-gray-500' => $num > $step,
                        ])>
                            @if ($num < $step)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="mt-1 hidden text-xs sm:block {{ $num === $step ? 'font-medium text-amber-700' : 'text-gray-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 h-1 rounded-full bg-gray-200">
                <div class="h-1 rounded-full bg-amber-500 transition-all" style="width: {{ (($step - 1) / 5) * 100 }}%"></div>
            </div>
        </div>

        @if ($error)
            <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $error }}</div>
        @endif

        {{-- ===== STEP 1: Plan Selection ===== --}}
        @if ($step === 1)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">{{ __('booking.choose_plan') }}</h1>
                <p class="mb-6 text-gray-600">{{ __('booking.choose_plan_subtitle') }}</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($plans as $plan)
                        <button wire:click="selectPlan({{ $plan['id'] }})" type="button" @class([
                            'rounded-xl border-2 p-5 text-left transition hover:shadow-md',
                            'border-amber-500 bg-amber-50' => $state->planId === $plan['id'],
                            'border-gray-200 bg-white' => $state->planId !== $plan['id'],
                        ])>
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $plan['name_translations'][app()->getLocale()] ?? $plan['name_translations']['fr'] ?? '' }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $plan['description_translations'][app()->getLocale()] ?? $plan['description_translations']['fr'] ?? '' }}</p>
                                </div>
                                @if ($plan['is_recommended'])
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ __('booking.recommended') }}</span>
                                @endif
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-lg font-bold text-amber-700">
                                    @if ($plan['price_centimes'] > 0)
                                        {{ number_format($plan['price_centimes'] / 100, 2, ',', ' ') }} MAD
                                    @else
                                        {{ __('booking.free') }}
                                    @endif
                                </span>
                                <span class="text-sm text-gray-500">{{ $plan['duration_minutes'] }} min</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== STEP 2: Category & Description ===== --}}
        @if ($step === 2)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ __('booking.your_request') }}</h1>

                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('booking.category_label') }}</label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (App\Enums\ServiceCategory::cases() as $cat)
                                <button wire:click="$set('state.category', '{{ $cat->value }}')" type="button" @class([
                                    'rounded-lg border-2 p-3 text-left transition',
                                    'border-amber-500 bg-amber-50' => $state->category === $cat->value,
                                    'border-gray-200' => $state->category !== $cat->value,
                                ])>
                                    <span class="font-medium text-gray-900">{{ __("services.categories.{$cat->value}") }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('state.category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700">{{ __('booking.description_label') }}</label>
                        <textarea wire:model="state.description" id="description" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                        @error('state.description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('booking.has_documents_label') }}</label>
                        <div class="flex gap-3">
                            @foreach ([true => __('booking.yes'), false => __('booking.no')] as $val => $label)
                                <button wire:click="$set('state.hasDocuments', {{ $val ? 'true' : 'false' }})" type="button" @class([
                                    'rounded-lg border-2 px-4 py-2 transition',
                                    'border-amber-500 bg-amber-50' => $state->hasDocuments === $val,
                                    'border-gray-200' => $state->hasDocuments !== $val,
                                ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('state.hasDocuments') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @php $plan = $this->getPlan(); @endphp
                    @if ($plan && $plan['format'] === 'both')
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('booking.format_label') }}</label>
                            <div class="flex gap-3">
                                @foreach (['online' => __('booking.online'), 'in_office' => __('booking.in_office')] as $val => $label)
                                    <button wire:click="$set('state.format', '{{ $val }}')" type="button" @class([
                                        'rounded-lg border-2 px-4 py-2 transition',
                                        'border-amber-500 bg-amber-50' => $state->format === $val,
                                        'border-gray-200' => $state->format !== $val,
                                    ])>
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            @error('state.format') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50">{{ __('booking.back') }}</button>
                    <button wire:click="submitStep2" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white transition hover:bg-amber-700">{{ __('booking.continue') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 3: Slot Picker ===== --}}
        @if ($step === 3)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ __('booking.choose_slot') }}</h1>

                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- Calendar --}}
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <button wire:click="previousMonth" type="button" class="rounded-lg p-2 hover:bg-gray-100">&larr;</button>
                            <span class="font-medium text-gray-900">{{ $this->calendarMonthLabel() }}</span>
                            <button wire:click="nextMonth" type="button" class="rounded-lg p-2 hover:bg-gray-100">&rarr;</button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-sm">
                            @php $dayLabels = [__('calendar.mon'), __('calendar.tue'), __('calendar.wed'), __('calendar.thu'), __('calendar.fri'), __('calendar.sat'), __('calendar.sun')]; @endphp
                            @foreach ($dayLabels as $label)
                                <div class="py-1 font-medium text-gray-500">{{ $label }}</div>
                            @endforeach
                            @php $daysInMonth = $this->calendarDays(); @endphp
                            @foreach ($daysInMonth as $dayData)
                                @if ($dayData['empty'])
                                    <div></div>
                                @else
                                    <button wire:click="selectDate('{{ $dayData['date']->format('Y-m-d') }}')" type="button" @class([
                                        'rounded-lg py-2 text-sm transition',
                                        'bg-amber-100 font-medium text-amber-700' => $selectedDate === $dayData['date']->format('Y-m-d'),
                                        'text-gray-400 hover:bg-gray-100' => !in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()),
                                        'text-gray-900 hover:bg-amber-50' => in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()),
                                    ])>
                                        {{ $dayData['date']->format('j') }}
                                        @if (in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()))
                                            <div class="mx-auto mt-0.5 h-1 w-1 rounded-full bg-amber-500"></div>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Time slots --}}
                    <div>
                        <h3 class="mb-3 font-medium text-gray-700">
                            {{ $selectedDate ? \Carbon\CarbonImmutable::parse($selectedDate)->locale(app()->getLocale())->isoFormat('dddd D MMMM') : __('booking.select_date') }}
                        </h3>
                        @if ($loadingSlots)
                            <div class="flex items-center justify-center py-12">
                                <svg class="h-8 w-8 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            </div>
                        @elseif (!$selectedDate)
                            <p class="text-gray-500">{{ __('booking.select_date_prompt') }}</p>
                        @elseif (empty($availableSlots))
                            <p class="text-gray-500">{{ __('booking.no_slots') }}</p>
                        @else
                            <div class="max-h-80 space-y-2 overflow-y-auto">
                                @foreach ($availableSlots as $slot)
                                    <button wire:click="selectSlot('{{ $slot['starts_at'] }}', '{{ $slot['ends_at'] }}')" type="button" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-left transition hover:border-amber-300 hover:bg-amber-50">
                                        <span class="font-medium text-gray-900">{{ $slot['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8">
                    <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50">{{ __('booking.back') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 4: Identity ===== --}}
        @if ($step === 4)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ __('booking.your_identity') }}</h1>

                <div class="space-y-4" wire:key="identity-form">
                    <div>
                        <label for="fullName" class="mb-1 block text-sm font-medium text-gray-700">{{ __('booking.full_name') }}</label>
                        <input wire:model="state.fullName" id="fullName" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-base text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @error('state.fullName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('booking.email') }}</label>
                        <input wire:model="state.email" id="email" type="email" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-base text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @error('state.email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('booking.phone') }}</label>
                        <input wire:model="state.phone" id="phone" type="tel" placeholder="06XXXXXXXX" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-base text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @error('state.phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('booking.preferred_channel') }}</label>
                        <div class="flex gap-3">
                            @foreach (['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $val => $label)
                                <button wire:click="$set('state.preferredChannel', '{{ $val }}')" type="button" @class([
                                    'rounded-lg border-2 px-4 py-2 text-sm transition',
                                    'border-amber-500 bg-amber-50' => $state->preferredChannel === $val,
                                    'border-gray-200' => $state->preferredChannel !== $val,
                                ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3 rounded-lg bg-gray-50 p-4">
                        <label class="flex items-start gap-3">
                            <input wire:model="state.acceptedTerms" type="checkbox" class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-gray-700">{!! __('booking.terms_accept') !!}</span>
                        </label>
                        @error('state.acceptedTerms') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <label class="flex items-start gap-3">
                            <input wire:model="state.acceptedPrivacy" type="checkbox" class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-gray-700">{!! __('booking.privacy_accept') !!}</span>
                        </label>
                        @error('state.acceptedPrivacy') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50">{{ __('booking.back') }}</button>
                    <button wire:click="submitStep4" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white transition hover:bg-amber-700">{{ __('booking.continue') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 5: Documents ===== --}}
        @if ($step === 5)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ __('booking.your_documents') }}</h1>

                @if ($state->hasDocuments === false)
                    <p class="mb-6 text-gray-600">{{ __('booking.documents_skip_note') }}</p>
                    <button wire:click="skipToPayment" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white transition hover:bg-amber-700">{{ __('booking.continue') }}</button>
                @else
                    <div class="mb-6" x-data="{ uploading: false, progress: 0 }">
                        <div class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-8" x-on:dragover.prevent x-on:drop.prevent="
                            $refs.fileInput.files = event.dataTransfer.files;
                            $refs.fileInput.dispatchEvent(new Event('change'));
                        ">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="mt-2 text-sm text-gray-600">{{ __('booking.drop_files') }}</p>
                                <input x-ref="fileInput" type="file" wire:model="files" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                <button type="button" x-on:click="$refs.fileInput.click()" class="mt-2 rounded-lg bg-amber-600 px-4 py-2 text-sm text-white">{{ __('booking.choose_files') }}</button>
                            </div>
                        </div>
                        @error('files.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                        @if ($temporaryFiles ?? false)
                            <div class="mt-4 space-y-2">
                                @foreach ($temporaryFiles as $file)
                                    <div class="flex items-center justify-between rounded-lg border p-3">
                                        <span class="text-sm text-gray-700">{{ $file['name'] }}</span>
                                        <span class="text-xs text-gray-500">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                                        <button wire:click="removeFile('{{ $file['uuid'] }}')" type="button" class="text-red-500 hover:text-red-700">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between">
                        <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50">{{ __('booking.back') }}</button>
                        <button wire:click="submitStep5" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white transition hover:bg-amber-700">{{ __('booking.continue') }}</button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== STEP 6: Payment ===== --}}
        @if ($step === 6)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ __('booking.payment') }}</h1>

                {{-- Summary --}}
                @php $plan = $this->getPlan(); @endphp
                <div class="mb-6 rounded-lg bg-gray-50 p-4">
                    <h3 class="font-medium text-gray-900">{{ $plan['name_translations'][app()->getLocale()] ?? $plan['name_translations']['fr'] ?? '' }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $plan['description_translations'][app()->getLocale()] ?? $plan['description_translations']['fr'] ?? '' }}</p>
                    <div class="mt-2 flex items-center justify-between border-t pt-2 text-sm">
                        <span class="text-gray-500">{{ __('booking.total') }}</span>
                        <span class="text-lg font-bold text-amber-700">
                            @if ($isFreePlan)
                                {{ __('booking.free') }}
                            @else
                                {{ number_format($this->getPlanPrice() / 100, 2, ',', ' ') }} MAD
                            @endif
                        </span>
                    </div>
                </div>

                @if ($isFreePlan)
                    <p class="mb-6 text-gray-600">{{ __('booking.free_plan_note') }}</p>
                    <div class="flex justify-between">
                        <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('booking.back') }}</button>
                        <button wire:click="confirmBooking" wire:loading.attr="disabled" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white hover:bg-amber-700">
                            <span wire:loading.remove>{{ __('booking.confirm') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @elseif ($showCashOption)
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('booking.payment_method') }}</label>
                        <div class="space-y-3">
                            <button wire:click="setPaymentMethod('card')" type="button" @class([
                                'flex w-full items-center rounded-lg border-2 p-4 transition',
                                'border-amber-500 bg-amber-50' => $state->paymentMethod === 'card',
                                'border-gray-200' => $state->paymentMethod !== 'card',
                            ])>
                                <span class="font-medium text-gray-900">{{ __('booking.card_payment') }}</span>
                                <span class="ml-auto text-sm text-gray-500">{{ __('booking.secured_stripe') }}</span>
                            </button>
                            <button wire:click="setPaymentMethod('cash')" type="button" @class([
                                'flex w-full items-center rounded-lg border-2 p-4 transition',
                                'border-amber-500 bg-amber-50' => $state->paymentMethod === 'cash',
                                'border-gray-200' => $state->paymentMethod !== 'cash',
                            ])>
                                <span class="font-medium text-gray-900">{{ __('booking.cash_payment') }}</span>
                                <span class="ml-auto text-sm text-gray-500">{{ __('booking.pay_at_office') }}</span>
                            </button>
                        </div>
                    </div>

                    @if ($state->paymentMethod === 'card')
                        <div class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                            {{ __('booking.card_info') }}
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('booking.back') }}</button>
                        <button wire:click="confirmBooking" wire:loading.attr="disabled" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white hover:bg-amber-700">
                            <span wire:loading.remove>{{ __('booking.confirm_and_pay') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @else
                    <div class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                        {{ __('booking.card_info') }}
                    </div>

                    <div class="flex justify-between">
                        <button wire:click="goBack" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('booking.back') }}</button>
                        <button wire:click="confirmBooking" wire:loading.attr="disabled" type="button" class="rounded-lg bg-amber-600 px-6 py-2 text-white hover:bg-amber-700">
                            <span wire:loading.remove>{{ __('booking.confirm_and_pay') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== STEP 7: Success ===== --}}
        @if ($step === 7)
            <div class="rounded-xl bg-white p-6 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="mb-2 text-2xl font-bold text-gray-900">{{ __('booking.confirmed_title') }}</h1>
                <p class="mb-6 text-gray-600">{{ __('booking.confirmed_subtitle') }}</p>

                <div class="mx-auto mb-6 max-w-md rounded-lg bg-gray-50 p-4 text-left">
                    <p class="text-sm text-gray-500">{{ __('booking.reference') }}</p>
                    <p class="text-lg font-bold text-amber-700">{{ $reference }}</p>

                    @php $plan = $this->getPlan(); @endphp
                    @if ($plan)
                        <div class="mt-3 border-t pt-3">
                            <p class="text-sm text-gray-600">{{ $plan['name_translations'][app()->getLocale()] ?? $plan['name_translations']['fr'] ?? '' }}</p>
                            @if ($state->slotStartsAt)
                                <p class="text-sm text-gray-600">{{ \Carbon\CarbonImmutable::parse($state->slotStartsAt)->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <p class="text-sm text-gray-600">{{ __('booking.next_steps') }}</p>
                    <a href="{{ route('portal.login', ['locale' => app()->getLocale()]) }}?email={{ urlencode($state->email) }}" class="inline-block rounded-lg bg-amber-600 px-6 py-2 text-white transition hover:bg-amber-700">
                        {{ __('booking.portal_link') }}
                    </a>
                </div>
            </div>
        @endif
    </div>

    @script
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('stripe-payment', async (data) => {
                const { clientSecret, reference } = data;
                try {
                    const stripe = Stripe('{{ config('services.stripe.key') }}');
                    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret);
                    if (error) {
                        Livewire.dispatch('handlePaymentError', { message: error.message });
                    } else if (paymentIntent.status === 'succeeded') {
                        Livewire.dispatch('handlePaymentSuccess', { reference });
                    }
                } catch (e) {
                    Livewire.dispatch('handlePaymentError', { message: e.message });
                }
            });
        });
    </script>
    @endscript
</div>
