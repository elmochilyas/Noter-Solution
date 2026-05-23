<div>
    @if ($succeeded)
        <div class="glass-card p-8 text-center">
            <div class="size-16 rounded-2xl bg-success-bg border border-success/20 flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-success font-semibold text-lg">{{ __('contact.form_success') }}</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="glass-card p-8 md:p-10 space-y-6" x-data x-init="window.turnstileCallback = (token) => $wire.set('turnstileToken', token)">
            <h2 class="text-xl font-semibold text-ink mb-1">{{ __('contact.form_title') }}</h2>

            {{-- Honeypot — invisible to users --}}
            <div class="absolute start-[-9999px]" aria-hidden="true">
                <label for="honeypot">Do not fill</label>
                <input type="text" id="honeypot" wire:model="honeypot" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">{{ __('contact.form_name') }}</label>
                    <input type="text" id="name" wire:model="name" autocomplete="name" class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast @error('name') border-danger @enderror">
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">{{ __('contact.form_email') }}</label>
                    <input type="email" id="email" wire:model="email" autocomplete="email" inputmode="email" class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast @error('email') border-danger @enderror">
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-stone-700 mb-1.5">{{ __('contact.form_subject') }}</label>
                <select id="subject" wire:model="subject" class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm text-ink focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast @error('subject') border-danger @enderror">
                    <option value="">{{ __('contact.form_subject') }}</option>
                    <option value="family">{{ __('contact.form_subject_family') }}</option>
                    <option value="realestate">{{ __('contact.form_subject_realestate') }}</option>
                    <option value="financial">{{ __('contact.form_subject_financial') }}</option>
                    <option value="contracts">{{ __('contact.form_subject_contracts') }}</option>
                    <option value="other">{{ __('contact.form_subject_other') }}</option>
                </select>
                @error('subject') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-stone-700 mb-1.5">{{ __('contact.form_message') }}</label>
                <textarea id="message" wire:model="message" rows="5" class="block w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-ink placeholder:text-stone-400 focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast resize-y @error('message') border-danger @enderror"></textarea>
                @error('message') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            {{-- Preferred contact channel --}}
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-2">{{ __('contact.form_preferred_contact') }}</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (['phone' => __('contact.form_preferred_phone'), 'email' => __('contact.form_preferred_email'), 'whatsapp' => __('contact.form_preferred_whatsapp')] as $val => $label)
                        <button wire:click="$set('preferredChannel', '{{ $val }}')" type="button" @class([
                            'rounded-xl border px-4 py-2 text-sm font-medium transition-fast',
                            'border-brass-400 bg-brass-50 text-brass-700' => $preferredChannel === $val,
                            'border-stone-200 bg-white text-stone-600 hover:border-brass-200 hover:bg-brass-50/50' => $preferredChannel !== $val,
                        ])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @error('preferredChannel') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            {{-- Double opt-in --}}
            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="acceptedMarketing" type="checkbox" class="mt-0.5 rounded border-stone-300 text-brass-600 focus:ring-brass-400 focus:ring-offset-0">
                <span class="text-sm text-stone-600">{{ __('contact.form_marketing_consent') }}</span>
            </label>
            @error('acceptedMarketing') <p class="text-xs text-danger">{{ $message }}</p> @enderror

            @error('turnstile') <p class="text-xs text-danger">{{ $message }}</p> @enderror

            <div class="flex justify-center" wire:ignore>
                <div x-init="$nextTick(() => {
                    if (typeof turnstile !== 'undefined' && document.getElementById('cf-turnstile')) {
                        turnstile.render('#cf-turnstile', {
                            sitekey: '{{ config('services.turnstile.site_key') }}',
                            callback: function(token) { $wire.set('turnstileToken', token); },
                        });
                    }
                })">
                    <div id="cf-turnstile"></div>
                </div>
            </div>

            <button type="submit" class="btn-brass w-full h-12 rounded-xl text-sm font-semibold scale-on-press inline-flex items-center justify-center disabled:opacity-50" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('contact.form_submit') }}</span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </span>
            </button>
        </form>
    @endif
</div>
