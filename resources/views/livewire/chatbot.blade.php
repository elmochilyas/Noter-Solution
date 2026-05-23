@php
    $isRtl = app()->getLocale() === 'ar';
@endphp

<div
    x-data="{
        open: @entangle('open'),
        disclaimerAccepted: @entangle('disclaimerAccepted'),
        init() {
            this.$watch('open', (val) => {
                if (val) {
                    document.addEventListener('keydown', this.handleEscape.bind(this));
                } else {
                    document.removeEventListener('keydown', this.handleEscape.bind(this));
                }
            });
        },
        handleEscape(e) {
            if (e.key === 'Escape') {
                this.open = false;
                $wire.close();
            }
        },
    }"
    class="fixed bottom-5 {{ $isRtl ? 'start-5' : 'end-5' }} z-50"
>
    <button
        wire:click="toggle"
        type="button"
        aria-label="{{ __('chatbot.toggle_button') }}"
        class="size-14 rounded-2xl shadow-xl shadow-brass-900/20 transition-all hover:scale-105 hover:shadow-brass-900/30 motion-reduce:transition-none motion-reduce:hover:scale-100 bg-gradient-to-br from-brass-500 to-brass-600 flex items-center justify-center"
    >
        @if ($open)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-parchment" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-parchment" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        @endif
    </button>

    @if ($open)
        <div
            class="absolute bottom-16 {{ $isRtl ? 'start-0' : 'end-0' }} w-[380px] max-w-[calc(100vw-2rem)] h-[600px] max-h-[calc(100vh-6rem)] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-stone-100 motion-reduce:transition-none"
            role="dialog"
            aria-label="{{ __('chatbot.dialog_label') }}"
            aria-live="polite"
            x-trap.noscroll="open"
        >
            @if (!$disclaimerAccepted)
                <div class="flex flex-col items-center justify-center p-8 text-center h-full">
                    <div class="size-16 rounded-2xl bg-brass-50 border border-brass-100 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold mb-2 text-ink">{{ __('chatbot.disclaimer_title') }}</h2>
                    <p class="text-sm mb-3 text-stone-500 leading-relaxed">{{ __('chatbot.disclaimer_text') }}</p>
                    <p class="text-xs mb-6 text-stone-400">{{ __('chatbot.disclaimer_privacy') }}</p>
                    <button
                        wire:click="acceptDisclaimer"
                        type="button"
                        class="btn-brass px-8 py-2.5 rounded-xl text-sm font-semibold scale-on-press"
                    >
                        {{ __('chatbot.disclaimer_accept') }}
                    </button>
                </div>
            @else
                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-stone-100 bg-gradient-to-r from-brass-50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="size-9 rounded-xl bg-gradient-to-br from-brass-500 to-brass-600 flex items-center justify-center shadow-sm">
                            <span class="text-parchment text-xs font-bold">SB</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ __('chatbot.header_title') }}</p>
                            <p class="text-xs text-stone-400">{{ __('chatbot.header_subtitle') }}</p>
                        </div>
                    </div>
                    <button wire:click="toggle" type="button" class="size-8 rounded-lg hover:bg-stone-100 flex items-center justify-center transition-fast" aria-label="{{ __('chatbot.close_button') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3" wire:loading.class="opacity-70">
                    @foreach ($messages as $msg)
                        <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[82%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed
                                {{ $msg['role'] === 'user'
                                    ? 'bg-gradient-to-br from-brass-500 to-brass-600 text-parchment rounded-br-sm'
                                    : 'bg-stone-50 border border-stone-100 text-ink rounded-bl-sm' }}">
                                {!! nl2br(e(is_string($msg['content'] ?? null) ? $msg['content'] : '')) !!}
                            </div>
                        </div>
                    @endforeach

                    @if ($isTyping)
                        <div class="flex justify-start">
                            <div class="rounded-2xl rounded-bl-sm px-4 py-3 bg-stone-50 border border-stone-100">
                                <div class="flex gap-1 motion-reduce:hidden">
                                    <span class="size-2 rounded-full animate-bounce bg-brass-400" style="animation-delay: 0ms;"></span>
                                    <span class="size-2 rounded-full animate-bounce bg-brass-400" style="animation-delay: 150ms;"></span>
                                    <span class="size-2 rounded-full animate-bounce bg-brass-400" style="animation-delay: 300ms;"></span>
                                </div>
                                <span class="hidden motion-reduce:block text-xs text-stone-400">{{ __('chatbot.typing_indicator') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (count($suggestions) > 0 && !$isTyping)
                        <div class="flex flex-wrap gap-2 pt-1">
                            @foreach ($suggestions as $suggestion)
                                <button
                                    wire:click="sendSuggestion('{{ e(is_string($suggestion) ? $suggestion : '') }}')"
                                    type="button"
                                    class="text-xs px-3 py-1.5 rounded-full border border-brass-200 bg-brass-50 text-brass-700 hover:bg-brass-100 hover:border-brass-300 transition-fast"
                                >
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($error)
                        <p class="text-xs text-danger text-center py-2">{{ $error }}</p>
                    @endif
                </div>

                {{-- Input --}}
                <div class="border-t border-stone-100 p-3 bg-stone-50/50">
                    <form wire:submit.prevent="send" class="flex gap-2">
                        <label for="chatbot-input" class="sr-only">{{ __('chatbot.input_label') }}</label>
                        <input
                            id="chatbot-input"
                            wire:model="input"
                            type="text"
                            autocomplete="off"
                            placeholder="{{ __('chatbot.input_placeholder') }}"
                            class="flex-1 px-4 py-2 text-sm rounded-xl border border-stone-200 bg-white focus:outline-none focus:ring-2 focus:ring-brass-400/20 focus:border-brass-400 transition-fast"
                        />
                        <button
                            type="submit"
                            @disabled($input === '' || $isTyping)
                            class="size-10 rounded-xl bg-gradient-to-br from-brass-500 to-brass-600 text-parchment flex items-center justify-center transition-fast disabled:opacity-40 hover:from-brass-600 hover:to-brass-700 scale-on-press"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </button>
                    </form>
                    <p class="text-xs text-center mt-2 text-stone-400">{{ __('chatbot.footer_note') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
