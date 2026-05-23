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
                    document.addEventListener('keydown', this.handleEscape);
                } else {
                    document.removeEventListener('keydown', this.handleEscape);
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
    class="fixed bottom-4 {{ $isRtl ? 'start-4' : 'end-4' }} z-50"
>
    <button
        wire:click="toggle"
        type="button"
        aria-label="{{ __('chatbot.toggle_button') }}"
        class="flex items-center justify-center w-14 h-14 rounded-full shadow-lg transition-transform hover:scale-105 motion-reduce:transition-none motion-reduce:hover:scale-100"
        style="background-color: #B68A3E;"
    >
        @if ($open)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        @endif
    </button>

    @if ($open)
        <div
            class="absolute bottom-16 {{ $isRtl ? 'start-0' : 'end-0' }} w-[380px] max-w-[calc(100vw-2rem)] h-[600px] max-h-[calc(100vh-8rem)] bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden border border-stone-200 motion-reduce:transition-none"
            role="dialog"
            aria-label="{{ __('chatbot.dialog_label') }}"
            aria-live="polite"
            x-trap.noscroll="open"
        >
            @if (!$disclaimerAccepted)
                <div class="flex flex-col items-center justify-center p-6 text-center h-full">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background-color: #F5F0E8;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color: #B68A3E;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>

                    <h2 class="text-lg font-semibold mb-2" style="color: #2C1810;">{{ __('chatbot.disclaimer_title') }}</h2>
                    <p class="text-sm mb-4" style="color: #78716C;">{{ __('chatbot.disclaimer_text') }}</p>
                    <p class="text-xs mb-6" style="color: #A8A29E;">{{ __('chatbot.disclaimer_privacy') }}</p>

                    <button
                        wire:click="acceptDisclaimer"
                        type="button"
                        class="px-6 py-2 rounded-full text-white font-medium transition-colors hover:opacity-90 motion-reduce:transition-none"
                        style="background-color: #B68A3E;"
                    >
                        {{ __('chatbot.disclaimer_accept') }}
                    </button>
                </div>
            @else
                <div class="flex items-center justify-between px-4 py-3 border-b border-stone-200" style="background-color: #F5F0E8;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: #B68A3E;">
                            <span class="text-white text-xs font-bold">SB</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium" style="color: #2C1810;">{{ __('chatbot.header_title') }}</p>
                            <p class="text-xs" style="color: #78716C;">{{ __('chatbot.header_subtitle') }}</p>
                        </div>
                    </div>
                    <button wire:click="toggle" type="button" class="p-1 rounded hover:bg-stone-200 transition-colors motion-reduce:transition-none" aria-label="{{ __('chatbot.close_button') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: #78716C;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3" wire:loading.class="opacity-70">
                    @foreach ($messages as $msg)
                        <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="max-w-[80%] rounded-2xl px-4 py-2 text-sm leading-relaxed"
                                @if ($msg['role'] === 'user')
                                    style="background-color: #B68A3E; color: white;"
                                @else
                                    style="background-color: #F5F0E8; color: #2C1810;"
                                @endif
                            >
                                {!! nl2br(e($msg['content'])) !!}
                            </div>
                        </div>
                    @endforeach

                    @if ($isTyping)
                        <div class="flex justify-start">
                            <div class="rounded-2xl px-4 py-3" style="background-color: #F5F0E8;">
                                <div class="flex gap-1 motion-reduce:hidden">
                                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #B68A3E; animation-delay: 0ms;"></span>
                                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #B68A3E; animation-delay: 150ms;"></span>
                                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #B68A3E; animation-delay: 300ms;"></span>
                                </div>
                                <div class="hidden motion-reduce:block text-sm" style="color: #78716C;">
                                    {{ __('chatbot.typing_indicator') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (count($suggestions) > 0 && !$isTyping)
                        <div class="flex flex-wrap gap-2 pt-2">
                            @foreach ($suggestions as $suggestion)
                                <button
                                    wire:click="sendSuggestion('{{ e($suggestion) }}')"
                                    type="button"
                                    class="text-xs px-3 py-1.5 rounded-full border transition-colors hover:opacity-80 motion-reduce:transition-none"
                                    style="border-color: #B68A3E; color: #B68A3E;"
                                >
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($error)
                        <p class="text-xs text-red-500 text-center">{{ $error }}</p>
                    @endif
                </div>

                <div class="border-t border-stone-200 p-3">
                    <form wire:submit.prevent="send" class="flex gap-2">
                        <label for="chatbot-input" class="sr-only">{{ __('chatbot.input_label') }}</label>
                        <input
                            id="chatbot-input"
                            wire:model="input"
                            wire:keydown.enter="send"
                            type="text"
                            autocomplete="off"
                            placeholder="{{ __('chatbot.input_placeholder') }}"
                            class="flex-1 px-4 py-2 text-sm rounded-full border border-stone-300 focus:outline-none focus:ring-2 transition-colors motion-reduce:transition-none"
                            style="focus:ring-color: #B68A3E;"
                        />
                        <button
                            type="submit"
                            disabled="{{ $input === '' || $isTyping ? 'disabled' : '' }}"
                            class="px-4 py-2 rounded-full text-white font-medium transition-opacity disabled:opacity-50 motion-reduce:transition-none"
                            style="background-color: #B68A3E;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                            </svg>
                        </button>
                    </form>
                    <p class="text-xs text-center mt-2" style="color: #A8A29E;">{{ __('chatbot.footer_note') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
