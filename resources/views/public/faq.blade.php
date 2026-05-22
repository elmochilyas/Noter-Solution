@extends('layouts.public')

@section('title', __('faq.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('faq.page_title'))

@section('content')
    {{-- Page intro --}}
    <section class="pt-32 pb-16 px-4 max-w-7xl mx-auto text-center">
        <span class="text-sm font-semibold uppercase tracking-widest text-brass-500 mb-6 block">{{ __('faq.page_title') }}</span>
        <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-6 max-w-3xl mx-auto leading-tight" style="font-family: var(--font-display-fr);">
            {{ __('faq.page_title') }}
        </h1>
    </section>

    {{-- Search + Filter --}}
    <section class="pb-8 px-4 max-w-3xl mx-auto" x-data="faqSearch()">
        <div class="flex flex-col gap-4">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute start-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="search" x-model="query" x-on:input.debounce.300ms="search" placeholder="{{ __('faq.search_placeholder') }}" class="w-full rounded-lg border border-stone-200 bg-white py-3 ps-12 pe-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast">
            </div>
            <div class="flex flex-wrap gap-2">
                <button x-on:click="filter = ''; search()" :class="filter === '' ? 'bg-brass-500 text-parchment' : 'bg-white text-stone-700 hover:bg-stone-100'" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-medium transition-fast">
                    {{ __('faq.all_categories') }}
                </button>
                @foreach ($categories as $cat)
                    <button x-on:click="filter = '{{ $cat }}'; search()" :class="filter === '{{ $cat }}' ? 'bg-brass-500 text-parchment' : 'bg-white text-stone-700 hover:bg-stone-100'" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-medium transition-fast">
                        {{ __("faq.category_{$cat}") }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- FAQ list --}}
        <div class="mt-8 border-t border-stone-200">
            <template x-for="faq in filteredResults" :key="faq.id">
                <div x-data="{ open: false }" class="border-b border-stone-200">
                    <button x-on:click="open = !open" class="w-full py-6 flex justify-between items-center text-start group" :aria-expanded="open">
                        <span class="text-lg font-medium text-ink" x-text="faq.question"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="pb-6">
                        <p class="text-sm text-stone-500 leading-relaxed" x-text="faq.answer"></p>
                    </div>
                </div>
            </template>
            <p x-show="filteredResults.length === 0" class="py-12 text-center text-stone-500">
                {{ __('faq.no_results') }}
            </p>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 bg-[#0E1B2C] px-4 mt-16">
        <div class="max-w-3xl mx-auto text-center flex flex-col items-center gap-8">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment">{{ __('home.cta_title') }}</h2>
            <p class="text-lg text-stone-300 max-w-xl">{{ __('home.cta_desc') }}</p>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast w-full sm:w-auto">
                {{ __('home.cta_button') }}
            </a>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('faqSearch', () => ({
            query: '',
            filter: '',
            results: @json($faqs->map(function ($group, $cat) {
                return $group->map(function ($faq) {
                    $locale = app()->getLocale();
                    return [
                        'id' => $faq->id,
                        'question' => $faq->question_translations[$locale] ?? $faq->question_translations['fr'] ?? '',
                        'answer' => $faq->answer_translations[$locale] ?? $faq->answer_translations['fr'] ?? '',
                        'category' => $faq->category,
                    ];
                })->values();
            })->flatten(1)),
            search() {
                // Already reactive via Alpine
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
