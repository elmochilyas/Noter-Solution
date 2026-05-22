<div x-data="{ open: false }" class="fixed bottom-6 end-6 z-40">
    {{-- Chat button --}}
    <button x-on:click="open = !open" class="size-14 rounded-full bg-brass-500 text-parchment shadow-lg hover:bg-brass-600 transition-all duration-200 flex items-center justify-center" aria-label="{{ __('common.chatbot_placeholder_title') }}">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Chat popup --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" class="absolute bottom-20 end-0 w-80 bg-white border border-stone-200 rounded-lg shadow-xl overflow-hidden">
        <div class="bg-ink text-parchment p-4">
            <h4 class="font-semibold text-sm">{{ __('common.chatbot_placeholder_title') }}</h4>
        </div>
        <div class="p-6 text-center text-sm text-stone-500 leading-relaxed">
            <p>{{ __('common.chatbot_placeholder_desc') }}</p>
            <a href="tel:0528380719" class="mt-4 inline-flex items-center justify-center rounded-md bg-brass-500 px-5 py-2 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('common.call') }}
            </a>
        </div>
    </div>
</div>
