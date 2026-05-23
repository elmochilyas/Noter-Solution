<div class="min-h-screen bg-gradient-to-b from-parchment/50 to-white py-6 sm:py-10" x-data="{ step: $wire.entangle('step') }">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        {{-- Progress indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                @php $labels = [1 => __('booking.step_plan'), 2 => __('booking.step_details'), 3 => __('booking.step_slot'), 4 => __('booking.step_identity'), 5 => __('booking.step_documents'), 6 => __('booking.step_payment')]; @endphp
                @foreach ($labels as $num => $label)
                    <div class="flex flex-col items-center gap-1">
                        <div @class([
                            'size-9 rounded-xl flex items-center justify-center text-xs font-semibold transition-all',
                            'bg-brass-500 text-parchment shadow-sm shadow-brass-300/50' => $num === $step,
                            'bg-brass-100 text-brass-600' => $num < $step,
                            'bg-stone-100 text-stone-400' => $num > $step,
                        ])>
                            @if ($num < $step)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="hidden text-xs sm:block font-medium {{ $num === $step ? 'text-brass-600' : 'text-stone-400' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
            <div class="h-1.5 rounded-full bg-stone-100">
                <div class="h-1.5 rounded-full bg-gradient-to-r from-brass-400 to-brass-600 transition-all duration-500" style="width: {{ (($step - 1) / 5) * 100 }}%"></div>
            </div>
        </div>

        @if ($error)
            <div class="mb-6 rounded-xl bg-danger-bg border border-danger/20 p-4 text-sm text-danger" role="alert">{{ $error }}</div>
        @endif

        {{-- ===== STEP 1: Plan Selection ===== --}}
        @if ($step === 1)
            <div wire:key="step-1" wire:transition.fade class="card-premium p-6 md:p-8">
                <h1 class="mb-1 text-2xl font-semibold text-ink">{{ __('booking.choose_plan') }}</h1>
                <p class="mb-6 text-sm text-stone-500">{{ __('booking.choose_plan_subtitle') }}</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($plans as $plan)
                        <button @click="$wire.call('selectPlan', {{ $plan['id'] }})" type="button" @class([
                            'rounded-2xl border p-5 text-start transition-all hover:shadow-md hover:-translate-y-0.5',
                            'border-brass-400 bg-brass-50 shadow-sm' => $state->planId === $plan['id'],
                            'border-stone-200 bg-white hover:border-brass-200' => $state->planId !== $plan['id'],
                        ])>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-ink">{{ locale_string($plan['name_translations'], app()->getLocale()) }}</h3>
                                    <p class="mt-1 text-xs text-stone-500 leading-relaxed">{{ locale_string($plan['description_translations'], app()->getLocale()) }}</p>
                                </div>
                                @if ($plan['is_recommended'])
                                    <span class="shrink-0 rounded-full bg-brass-100 border border-brass-200 px-2 py-0.5 text-xs font-semibold text-brass-700">{{ __('booking.recommended') }}</span>
                                @endif
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3">
                                <span class="text-lg font-semibold text-brass-600">
                                    @if ($plan['price_centimes'] > 0)
                                        {{ number_format($plan['price_centimes'] / 100, 2, ',', ' ') }} {{ __('plans.currency') }}
                                    @else
                                        {{ __('booking.free') }}
                                    @endif
                                </span>
                                <span class="text-xs text-stone-400">{{ $plan['duration_minutes'] }} min</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== STEP 2: Category & Description ===== --}}
        @if ($step === 2)
            <div class="card-premium p-6 md:p-8">
                <h1 class="mb-6 text-2xl font-semibold text-ink">{{ __('booking.your_request') }}</h1>

                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.category_label') }}</label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (App\Enums\ServiceCategory::cases() as $cat)
                                <button @click="$wire.call('selectCategory', '{{ $cat->value }}')" type="button" @class([
                                    'rounded-xl border p-3.5 text-start transition-all',
                                    'border-brass-400 bg-brass-50' => $state->category === $cat->value,
                                    'border-stone-200 hover:border-brass-200 hover:bg-brass-50/50' => $state->category !== $cat->value,
                                ])>
                                    <span class="font-medium text-ink text-sm">{{ $cat->label() }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('state.category') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.description_label') }}</label>
                        <textarea wire:model="state.description" id="description" rows="4" class="block w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-ink focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 resize-none"></textarea>
                        @error('state.description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.has_documents_label') }}</label>
                        <div class="flex gap-3">
                            <button @click="$wire.call('markHasDocuments')" type="button" @class([
                                'rounded-xl border px-5 py-2.5 text-sm font-medium transition-all',
                                'border-brass-400 bg-brass-50 text-brass-700' => $state->hasDocuments === true,
                                'border-stone-200 text-stone-600 hover:border-brass-200' => $state->hasDocuments !== true,
                            ])>
                                {{ __('booking.yes') }}
                            </button>
                            <button @click="$wire.call('markNoDocuments')" type="button" @class([
                                'rounded-xl border px-5 py-2.5 text-sm font-medium transition-all',
                                'border-brass-400 bg-brass-50 text-brass-700' => $state->hasDocuments === false,
                                'border-stone-200 text-stone-600 hover:border-brass-200' => $state->hasDocuments !== false,
                            ])>
                                {{ __('booking.no') }}
                            </button>
                        </div>
                        @error('state.hasDocuments') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @php $plan = $this->getPlan(); @endphp
                    @if ($plan && $plan['format'] === 'both')
                        <div>
                            <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.format_label') }}</label>
                            <div class="flex gap-3">
                                @foreach (['online' => __('booking.online'), 'in_office' => __('booking.in_office')] as $val => $label)
                                    <button @click="$wire.call('selectFormat', '{{ $val }}')" type="button" @class([
                                        'rounded-xl border px-5 py-2.5 text-sm font-medium transition-all',
                                        'border-brass-400 bg-brass-50 text-brass-700' => $state->format === $val,
                                        'border-stone-200 text-stone-600 hover:border-brass-200' => $state->format !== $val,
                                    ])>
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            @error('state.format') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-between">
                    <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                    <button @click="$wire.call('submitStep2')" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.continue') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 3: Slot Picker ===== --}}
        @if ($step === 3)
            <div wire:key="step-3" wire:transition.fade class="card-premium p-6 md:p-8">
                <h1 class="mb-6 text-2xl font-semibold text-ink">{{ __('booking.choose_slot') }}</h1>

                <div class="grid gap-8 lg:grid-cols-2">
                    {{-- Calendar --}}
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <button @click="$wire.call('previousMonth')" type="button" class="size-9 rounded-xl border border-stone-200 flex items-center justify-center text-stone-500 hover:bg-stone-50 hover:border-stone-300 transition-fast">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="font-semibold text-ink capitalize">{{ $this->calendarMonthLabel() }}</span>
                            <button @click="$wire.call('nextMonth')" type="button" class="size-9 rounded-xl border border-stone-200 flex items-center justify-center text-stone-500 hover:bg-stone-50 hover:border-stone-300 transition-fast">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs">
                            @php $dayLabels = [__('calendar.mon'), __('calendar.tue'), __('calendar.wed'), __('calendar.thu'), __('calendar.fri'), __('calendar.sat'), __('calendar.sun')]; @endphp
                            @foreach ($dayLabels as $label)
                                <div class="py-1.5 font-semibold text-stone-400 uppercase tracking-wide">{{ $label }}</div>
                            @endforeach
                            @php $daysInMonth = $this->calendarDays(); @endphp
                            @foreach ($daysInMonth as $dayData)
                                @if ($dayData['empty'])
                                    <div></div>
                                @else
                                    <button @click="$wire.call('selectDate', '{{ $dayData['date']->format('Y-m-d') }}')" type="button" @class([
                                        'rounded-lg py-2 text-sm transition-all relative',
                                        'bg-brass-500 text-parchment font-semibold' => $selectedDate === $dayData['date']->format('Y-m-d'),
                                        'text-stone-300 cursor-default' => !in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()) && $selectedDate !== $dayData['date']->format('Y-m-d'),
                                        'text-ink hover:bg-brass-50 font-medium' => in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()) && $selectedDate !== $dayData['date']->format('Y-m-d'),
                                    ])>
                                        {{ $dayData['date']->format('j') }}
                                        @if (in_array($dayData['date']->format('Y-m-d'), $this->daysWithSlots()) && $selectedDate !== $dayData['date']->format('Y-m-d'))
                                            <div class="absolute bottom-1 left-1/2 -translate-x-1/2 size-1 rounded-full bg-brass-500"></div>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Time slots --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-stone-500">
                            {{ $selectedDate ? \Carbon\CarbonImmutable::parse($selectedDate)->locale(app()->getLocale())->isoFormat('dddd D MMMM') : __('booking.select_date') }}
                        </h3>
                        @if ($loadingSlots)
                            <div class="flex items-center justify-center py-12">
                                <svg class="h-7 w-7 animate-spin text-brass-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            </div>
                        @elseif (!$selectedDate)
                            <p class="text-sm text-stone-400">{{ __('booking.select_date_prompt') }}</p>
                        @elseif (empty($availableSlots))
                            <p class="text-sm text-stone-400">{{ __('booking.no_slots') }}</p>
                        @else
                            <div class="max-h-72 space-y-2 overflow-y-auto pe-1">
                                @foreach ($availableSlots as $slot)
                                    <button @click="$wire.call('selectSlot', '{{ $slot['starts_at'] }}', '{{ $slot['ends_at'] }}')" type="button" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-start transition-all hover:border-brass-300 hover:bg-brass-50/50 text-sm font-medium text-ink">
                                        {{ $slot['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8">
                    <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 4: Identity ===== --}}
        @if ($step === 4)
            <div wire:key="step-4" wire:transition.fade class="card-premium p-6 md:p-8">
                <h1 class="mb-6 text-2xl font-semibold text-ink">{{ __('booking.your_identity') }}</h1>

                <div class="space-y-5" wire:key="identity-form">
                    <div>
                        <label for="fullName" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.full_name') }}</label>
                        <input wire:model="state.fullName" id="fullName" type="text" class="block h-11 w-full rounded-xl border border-stone-200 px-4 text-sm text-ink focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20">
                        @error('state.fullName') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.email') }}</label>
                        <input wire:model="state.email" id="email" type="email" class="block h-11 w-full rounded-xl border border-stone-200 px-4 text-sm text-ink focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20">
                        @error('state.email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.phone') }}</label>
                        <input wire:model="state.phone" id="phone" type="tel" placeholder="06XXXXXXXX" class="block h-11 w-full rounded-xl border border-stone-200 px-4 text-sm text-ink focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20">
                        @error('state.phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.preferred_channel') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['email' => __('booking.channels.email'), 'sms' => __('booking.channels.sms'), 'whatsapp' => __('booking.channels.whatsapp')] as $val => $label)
                                <button @click="$wire.call('selectChannel', '{{ $val }}')" type="button" @class([
                                    'rounded-xl border px-4 py-2 text-sm font-medium transition-all',
                                    'border-brass-400 bg-brass-50 text-brass-700' => $state->preferredChannel === $val,
                                    'border-stone-200 text-stone-600 hover:border-brass-200 hover:bg-brass-50/50' => $state->preferredChannel !== $val,
                                ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3 rounded-2xl bg-stone-50 border border-stone-100 p-5">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input wire:model="state.acceptedTerms" type="checkbox" class="mt-0.5 rounded border-stone-300 text-brass-600 focus:ring-brass-400 focus:ring-offset-0">
                            <span class="text-sm text-stone-600">{!! __('booking.terms_accept') !!}</span>
                        </label>
                        @error('state.acceptedTerms') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input wire:model="state.acceptedPrivacy" type="checkbox" class="mt-0.5 rounded border-stone-300 text-brass-600 focus:ring-brass-400 focus:ring-offset-0">
                            <span class="text-sm text-stone-600">{!! __('booking.privacy_accept') !!}</span>
                        </label>
                        @error('state.acceptedPrivacy') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                    <button @click="$wire.call('submitStep4')" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.continue') }}</button>
                </div>
            </div>
        @endif

        {{-- ===== STEP 5: Documents ===== --}}
        @if ($step === 5)
            <div wire:key="step-5" wire:transition.fade class="card-premium p-6 md:p-8">
                <h1 class="mb-6 text-2xl font-semibold text-ink">{{ __('booking.your_documents') }}</h1>

                @if ($state->hasDocuments === false)
                    <p class="mb-6 text-sm text-stone-500">{{ __('booking.documents_skip_note') }}</p>
                    <button @click="$wire.call('skipToPayment')" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.continue') }}</button>
                @else
                    <div class="mb-6" x-data="{ uploading: false, progress: 0 }">
                        <div class="flex items-center justify-center rounded-2xl border-2 border-dashed border-stone-200 bg-stone-50 p-10 hover:border-brass-300 hover:bg-brass-50/30 transition-fast"
                             x-on:dragover.prevent x-on:drop.prevent="
                                $refs.fileInput.files = event.dataTransfer.files;
                                $refs.fileInput.dispatchEvent(new Event('change'));
                             ">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-sm text-stone-500 mb-3">{{ __('booking.drop_files') }}</p>
                                <input x-ref="fileInput" type="file" wire:model="files" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                <button type="button" x-on:click="$refs.fileInput.click()" class="btn-brass px-5 py-2 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.choose_files') }}</button>
                            </div>
                        </div>
                        @error('files.*') <p class="mt-2 text-xs text-danger">{{ $message }}</p> @enderror

                        @if ($temporaryFiles ?? false)
                            <div class="mt-4 space-y-2">
                                @foreach ($temporaryFiles as $file)
                                    <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-3">
                                        <span class="text-sm text-ink font-medium truncate">{{ $file['name'] }}</span>
                                        <span class="text-xs text-stone-400 mx-3">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                                        <button @click="$wire.call('removeFile', '{{ $file['uuid'] }}')" type="button" class="size-6 rounded-lg bg-danger-bg text-danger flex items-center justify-center hover:bg-danger hover:text-white transition-fast shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between">
                        <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                        <button @click="$wire.call('submitStep5')" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.continue') }}</button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== STEP 6: Payment ===== --}}
        @if ($step === 6)
            <div wire:key="step-6" wire:transition.fade class="card-premium p-6 md:p-8">
                <h1 class="mb-6 text-2xl font-semibold text-ink">{{ __('booking.payment') }}</h1>

                {{-- Summary --}}
                @php $plan = $this->getPlan(); @endphp
                <div class="mb-6 rounded-2xl bg-stone-50 border border-stone-100 p-5">
                    <h3 class="font-semibold text-ink">{{ locale_string($plan['name_translations'], app()->getLocale()) }}</h3>
                    <p class="mt-1 text-sm text-stone-500">{{ locale_string($plan['description_translations'], app()->getLocale()) }}</p>
                    <div class="mt-3 flex items-center justify-between border-t border-stone-200 pt-3 text-sm">
                        <span class="text-stone-500">{{ __('booking.total') }}</span>
                        <span class="text-xl font-semibold text-brass-600">
                            @if ($isFreePlan)
                                {{ __('booking.free') }}
                            @else
                                {{ number_format($this->getPlanPrice() / 100, 2, ',', ' ') }} {{ __('plans.currency') }}
                            @endif
                        </span>
                    </div>
                </div>

                @if ($isFreePlan)
                    <p class="mb-6 text-sm text-stone-500">{{ __('booking.free_plan_note') }}</p>
                    <div class="flex justify-between">
                        <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                        <button @click="$wire.call('confirmBooking')" wire:loading.attr="disabled" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">
                            <span wire:loading.remove>{{ __('booking.confirm') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @elseif ($showCashOption)
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.payment_method') }}</label>
                        <div class="space-y-3">
                            <button @click="$wire.call('setPaymentMethod', 'card')" type="button" @class([
                                'flex w-full items-center rounded-2xl border p-4 transition-all',
                                'border-brass-400 bg-brass-50' => $state->paymentMethod === 'card',
                                'border-stone-200 hover:border-brass-200' => $state->paymentMethod !== 'card',
                            ])>
                                <span class="font-medium text-ink text-sm">{{ __('booking.card_payment') }}</span>
                                <span class="ms-auto text-xs text-stone-400">{{ __('booking.secured_stripe') }}</span>
                            </button>
                            <button @click="$wire.call('setPaymentMethod', 'cash')" type="button" @class([
                                'flex w-full items-center rounded-2xl border p-4 transition-all',
                                'border-brass-400 bg-brass-50' => $state->paymentMethod === 'cash',
                                'border-stone-200 hover:border-brass-200' => $state->paymentMethod !== 'cash',
                            ])>
                                <span class="font-medium text-ink text-sm">{{ __('booking.cash_payment') }}</span>
                                <span class="ms-auto text-xs text-stone-400">{{ __('booking.pay_at_office') }}</span>
                            </button>
                        </div>
                    </div>

                    @if ($state->paymentMethod === 'card')
                        <div class="mb-4 rounded-xl bg-info-bg border border-info/20 p-4 text-sm text-info">
                            {{ __('booking.card_info') }}
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                        <button @click="$wire.call('confirmBooking')" wire:loading.attr="disabled" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">
                            <span wire:loading.remove>{{ __('booking.confirm_and_pay') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @else
                    <div class="mb-4 rounded-xl bg-info-bg border border-info/20 p-4 text-sm text-info">
                        {{ __('booking.card_info') }}
                    </div>

                    <div class="flex justify-between">
                        <button @click="$wire.call('goBack')" type="button" class="btn-ghost px-4 py-2.5 rounded-xl text-sm font-semibold scale-on-press">{{ __('booking.back') }}</button>
                        <button @click="$wire.call('confirmBooking')" wire:loading.attr="disabled" type="button" class="btn-brass px-6 py-2.5 rounded-xl text-sm font-semibold scale-on-press">
                            <span wire:loading.remove>{{ __('booking.confirm_and_pay') }}</span>
                            <span wire:loading>{{ __('booking.processing') }}</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== STEP 7: Success ===== --}}
        @if ($step === 7)
            <div wire:key="step-7" wire:transition.fade class="card-premium p-8 text-center">
                <div class="size-20 rounded-2xl bg-success-bg border border-success/20 flex items-center justify-center mx-auto mb-6">
                    <svg class="h-9 w-9 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="mb-2 text-2xl font-semibold text-ink">{{ __('booking.confirmed_title') }}</h1>
                <p class="mb-8 text-sm text-stone-500 max-w-sm mx-auto">{{ __('booking.confirmed_subtitle') }}</p>

                <div class="mx-auto mb-8 max-w-md rounded-2xl bg-stone-50 border border-stone-100 p-5 text-start">
                    <p class="text-xs text-stone-400 mb-1">{{ __('booking.reference') }}</p>
                    <p class="text-lg font-semibold text-brass-600 font-mono">{{ $reference }}</p>

                    @php $plan = $this->getPlan(); @endphp
                    @if ($plan)
                        <div class="mt-4 pt-4 border-t border-stone-200 space-y-1">
                            <p class="text-sm font-medium text-ink">{{ locale_string($plan['name_translations'], app()->getLocale()) }}</p>
                            @if ($state->slotStartsAt)
                                <p class="text-sm text-stone-500">{{ \Carbon\CarbonImmutable::parse($state->slotStartsAt)->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <p class="text-sm text-stone-500">{{ __('booking.next_steps') }}</p>
                    <a href="{{ route('portal.login', ['locale' => app()->getLocale()]) }}?email={{ urlencode($state->email) }}" class="btn-brass inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold scale-on-press">
                        {{ __('booking.portal_link') }}
                    </a>
                </div>
            </div>
        @endif
    </div>

    @script
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
    @endscript
</div>
