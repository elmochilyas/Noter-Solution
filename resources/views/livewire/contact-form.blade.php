<div>
    @if ($succeeded)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-green-800 font-medium">{{ __('contact.form_success') }}</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="bg-white border border-stone-200 rounded-lg p-8 md:p-12 space-y-6" x-data x-init="window.turnstileCallback = (token) => $wire.set('turnstileToken', token)">
            <h2 class="text-2xl font-semibold text-ink mb-2">{{ __('contact.form_title') }}</h2>

            {{-- Honeypot — invisible to users --}}
            <div class="absolute start-[-9999px]" aria-hidden="true">
                <label for="honeypot">Do not fill</label>
                <input type="text" id="honeypot" wire:model="honeypot" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-ink mb-1.5">{{ __('contact.form_name') }}</label>
                    <input type="text" id="name" wire:model="name" autocomplete="name" class="w-full rounded-md border border-stone-200 bg-parchment px-4 py-2.5 text-sm text-ink placeholder:text-stone-400 focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-ink mb-1.5">{{ __('contact.form_email') }}</label>
                    <input type="email" id="email" wire:model="email" autocomplete="email" inputmode="email" class="w-full rounded-md border border-stone-200 bg-parchment px-4 py-2.5 text-sm text-ink placeholder:text-stone-400 focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast @error('email') border-red-400 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-ink mb-1.5">{{ __('contact.form_subject') }}</label>
                <select id="subject" wire:model="subject" class="w-full rounded-md border border-stone-200 bg-parchment px-4 py-2.5 text-sm text-ink focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast @error('subject') border-red-400 @enderror">
                    <option value="">{{ __('contact.form_subject') }}</option>
                    <option value="family">{{ __('contact.form_subject_family') }}</option>
                    <option value="realestate">{{ __('contact.form_subject_realestate') }}</option>
                    <option value="financial">{{ __('contact.form_subject_financial') }}</option>
                    <option value="contracts">{{ __('contact.form_subject_contracts') }}</option>
                    <option value="other">{{ __('contact.form_subject_other') }}</option>
                </select>
                @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-ink mb-1.5">{{ __('contact.form_message') }}</label>
                <textarea id="message" wire:model="message" rows="5" class="w-full rounded-md border border-stone-200 bg-parchment px-4 py-2.5 text-sm text-ink placeholder:text-stone-400 focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast resize-y @error('message') border-red-400 @enderror"></textarea>
                @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            @error('turnstile') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

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

            <button type="submit" class="w-full h-12 rounded-md bg-brass-500 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast inline-flex items-center justify-center disabled:opacity-50" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('contact.form_submit') }}</span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </span>
            </button>
        </form>
    @endif
</div>
