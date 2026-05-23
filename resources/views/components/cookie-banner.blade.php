<div x-data="{ show: !localStorage.getItem('cookie_consent') }"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-4 inset-x-4 z-50 md:bottom-6 md:inset-x-auto md:start-6 md:max-w-md">
    <div class="glass-card rounded-2xl p-5 shadow-2xl shadow-ink/15">
        <div class="flex gap-4 items-start">
            {{-- Cookie icon --}}
            <div class="shrink-0 mt-0.5 flex size-9 items-center justify-center rounded-xl bg-brass-50 border border-brass-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-relaxed text-stone-700">
                    {{ __('legal.cookie_banner_text') }}
                    <a href="/{{ app()->getLocale() }}/politique-confidentialite"
                       class="text-brass-600 underline underline-offset-2 hover:text-brass-700 transition-fast font-medium">
                        {{ __('legal.cookie_banner_link') }}
                    </a>.
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <button x-on:click="localStorage.setItem('cookie_consent', '1'); show = false"
                            class="btn-brass text-xs px-4 py-1.5 rounded-lg scale-on-press flex-1 justify-center">
                        {{ __('legal.cookie_banner_accept') }}
                    </button>
                    <button x-on:click="localStorage.setItem('cookie_consent', '0'); show = false"
                            class="btn-ghost text-xs px-4 py-1.5 rounded-lg scale-on-press">
                        Refuser
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
