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
                <div
                    x-ref="messagesContainer"
                    x-init="
                        $nextTick(() => $el.scrollTo({ top: $el.scrollHeight }));
                        new MutationObserver(() => $nextTick(() => { $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' }); }))
                            .observe($el, { childList: true, subtree: true, attributes: false });
                    "
                    class="flex-1 overflow-y-auto p-4 space-y-3 motion-reduce:scroll-auto scroll-smooth"
                    wire:loading.class="opacity-70"
                >
                    @foreach ($messages as $msg)
                        <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[82%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed
                                {{ $msg['role'] === 'user'
                                    ? 'bg-gradient-to-br from-brass-500 to-brass-600 text-parchment rounded-br-sm'
                                    : 'bg-stone-50 border border-stone-100 text-ink rounded-bl-sm' }}">
                                {!! $msg['role'] === 'assistant' ? Str::of(e($msg['content']))->markdown(['html_input' => 'strip', 'allow_unsafe_links' => false]) : nl2br(e($msg['content'] ?? '')) !!}
                            </div>
                        </div>
                    @endforeach

                    {{-- Plan Card --}}
                    @if ($planCard)
                        <div class="border-l-2 border-brass-500 bg-white border border-stone-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-base font-semibold text-ink">{{ $planCard['name'] }}</h4>
                                @if ($planCard['format_icon'] === 'video')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9.75a2.25 2.25 0 002.25-2.25V7.5a2.25 2.25 0 00-2.25-2.25H4.5A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                @endif
                            </div>
                            <p class="text-2xl font-display text-ink">{{ $planCard['price'] }}</p>
                            <p class="text-sm text-stone-500">{{ $planCard['duration_minutes'] }} min</p>
                            @if ($planCard['reason'])
                                <p class="text-sm text-stone-500 italic mt-2 line-clamp-2">{{ $planCard['reason'] }}</p>
                            @endif
                            <div class="mt-3 {{ $isRtl ? 'text-start' : 'text-end' }}">
                                <a
                                    href="{{ $planCard['booking_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    onclick="if (typeof plausible !== 'undefined') plausible('chatbot_plan_clicked', {props: {slug: '{{ $planCard['slug'] ?? '' }}', category: '{{ $planCard['category'] ?? '' }}', format: '{{ $planCard['format'] ?? '' }}'}})"
                                    class="inline-flex items-center px-5 py-2.5 rounded-md bg-brass-500 text-parchment text-sm font-semibold hover:bg-brass-600 transition-fast focus:ring-2 focus:ring-brass-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                                    {{ __('chatbot.recommendation_book_button') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Escalation Panel --}}
                    @if ($escalationPanel)
                        <div class="bg-stone-50 border border-stone-200 rounded-lg p-4">
                            <p class="text-sm text-stone-700 mb-3">{{ __('chatbot.escalation_suggestion') }}</p>
                            <div class="space-y-2">
                                <a href="tel:0528380719" class="flex items-center gap-2 text-sm text-ink hover:text-brass-600 transition-fast">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                    <span>05 28 38 07 19</span>
                                </a>
                                <a href="https://wa.me/212666120661" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-ink hover:text-brass-600 transition-fast">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                    </svg>
                                    <span>WhatsApp</span>
                                </a>
                                <a href="/{{ app()->getLocale() }}/book" class="flex items-center gap-2 text-sm text-ink hover:text-brass-600 transition-fast">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span>{{ __('chatbot.recommendation_book_button') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Typing Indicator --}}
                    @if ($isTyping)
                        <div class="flex justify-start">
                            <div class="rounded-2xl rounded-bl-sm px-4 py-3 bg-stone-50 border border-stone-100">
                                <div class="flex gap-1 motion-reduce:hidden">
                                    <span class="size-2 rounded-full bg-brass-400 animate-pulse" style="animation-delay: 0ms;"></span>
                                    <span class="size-2 rounded-full bg-brass-400 animate-pulse" style="animation-delay: 150ms;"></span>
                                    <span class="size-2 rounded-full bg-brass-400 animate-pulse" style="animation-delay: 300ms;"></span>
                                </div>
                                <span class="hidden motion-reduce:block text-xs text-stone-400">{{ __('chatbot.typing_indicator') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Suggestion Chips --}}
                    @if (count($suggestions) > 0 && !$isTyping && !$escalationPanel)
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

                    {{-- Out-of-scope single chip --}}
                    @if ($isOutOfScope && !$isTyping)
                        <div class="flex flex-wrap gap-2 pt-1">
                            <button
                                wire:click="sendSuggestion('{{ __('chatbot.suggestion_speak_to_human') }}')"
                                type="button"
                                class="text-xs px-3 py-1.5 rounded-full border border-brass-200 bg-brass-50 text-brass-700 hover:bg-brass-100 hover:border-brass-300 transition-fast"
                            >
                                {{ __('chatbot.suggestion_speak_to_human') }}
                            </button>
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
