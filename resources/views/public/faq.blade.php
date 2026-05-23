@extends('layouts.public')

@section('title', __('faq.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('faq.page_title'))

@section('content')
    <x-public.page-hero
        :label="__('faq.page_title')"
        :title="__('faq.page_title')"
    />

    {{-- Search + Filter --}}
    <section class="pb-8 px-4 max-w-3xl mx-auto reveal-up" x-data="faqSearch()">
        <div class="flex flex-col gap-4">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute start-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="search" x-model="query" x-on:input.debounce.300ms="search" placeholder="{{ __('faq.search_placeholder') }}" class="w-full rounded-xl border border-stone-200 bg-white py-3 ps-12 pe-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast shadow-sm">
            </div>
            <div class="flex flex-wrap gap-2">
                <button x-on:click="filter = ''; search()" :class="filter === '' ? 'bg-brass-500 text-parchment border-brass-500 shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-50 hover:border-stone-300'" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-semibold transition-fast">
                    {{ __('faq.all_categories') }}
                </button>
                @foreach ($categories as $cat)
                    <button x-on:click="filter = '{{ $cat }}'; search()" :class="filter === '{{ $cat }}' ? 'bg-brass-500 text-parchment border-brass-500 shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-50 hover:border-stone-300'" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-semibold transition-fast">
                        {{ __("faq.category_{$cat}") }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- FAQ list --}}
        <div class="mt-8 divide-y divide-stone-100">
            <template x-for="faq in filteredResults" :key="faq.id">
                <div x-data="{ open: false }" class="group">
                    <button x-on:click="open = !open" class="w-full py-6 flex justify-between items-center text-start" :aria-expanded="open">
                        <span class="text-base font-medium text-ink group-hover:text-brass-600 transition-fast pe-6" x-text="faq.question"></span>
                        <div class="shrink-0 size-8 rounded-full bg-stone-50 border border-stone-100 flex items-center justify-center transition-fast" :class="open ? 'bg-brass-50 border-brass-100 rotate-180' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition-all ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="pb-6">
                        <p class="text-sm text-stone-500 leading-relaxed" x-text="faq.answer"></p>
                    </div>
                </div>
            </template>
            <p x-show="filteredResults.length === 0" class="py-16 text-center text-stone-400 text-sm">
                {{ __('faq.no_results') }}
            </p>
        </div>
    </section>

    <x-public.cta-section
        :title="__('home.cta_title')"
        :description="__('home.cta_desc')"
        :buttonText="__('home.cta_button')"
    />
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('alpine:init', () => {
        Alpine.data('faqSearch', () => ({
            query: '',
            filter: '',
            results: {{ Js::from($faqs->map(function ($group, $cat) {
                return $group->map(function ($faq) {
                    $locale = app()->getLocale();
                    return [
                        'id' => $faq->id,
                        'question' => locale_string($faq->question_translations, $locale),
                        'answer' => locale_string($faq->answer_translations, $locale),
                        'category' => $faq->category,
                    ];
                })->values();
            })->flatten(1)) }},
            search() {
                // reactive via Alpine getter
            },
            get filteredResults() {
                let items = this.results;
                if (this.filter) {
                    items = items.filter(f => f.category === this.filter);
                }
                if (this.query.trim()) {
                    const q = this.query.toLowerCase();
                    items = items.filter(f => f.question.toLowerCase().includes(q) || f.answer.toLowerCase().includes(q));
                }
                return items;
            }
        }));
    });
</script>
@endpush
