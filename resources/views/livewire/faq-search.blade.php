<div>
    {{-- Search --}}
    <div class="relative mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute start-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        <input type="search" wire:model.live.debounce.300ms="query" placeholder="{{ __('faq.search_placeholder') }}" class="w-full rounded-lg border border-stone-200 bg-white py-3 ps-12 pe-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-500 focus:outline-none focus:ring-2 focus:ring-brass-500/20 transition-fast">
    </div>

    {{-- Category filters --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <button wire:click="clearCategory" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-medium transition-fast @if(!$category) bg-brass-500 text-parchment @else bg-white text-stone-700 hover:bg-stone-100 @endif">
            {{ __('faq.all_categories') }}
        </button>
        @foreach ($categories as $cat)
            <button wire:click="filterByCategory('{{ $cat }}')" class="rounded-full border border-stone-200 px-4 py-1.5 text-xs font-medium transition-fast @if($category === $cat) bg-brass-500 text-parchment @else bg-white text-stone-700 hover:bg-stone-100 @endif">
                {{ __("faq.category_{$cat}") }}
            </button>
        @endforeach
    </div>

    {{-- FAQ list --}}
    <div class="border-t border-stone-200">
        @forelse ($faqs as $faq)
            <div x-data="{ open: false }" class="border-b border-stone-200">
                <button x-on:click="open = !open; $wire.incrementViewCount({{ $faq->id }})" class="w-full py-6 flex justify-between items-center text-start group" :aria-expanded="open">
                    <span class="text-lg font-medium text-ink">{{ $faq->question_translations[app()->getLocale()] ?? $faq->question_translations['fr'] ?? '' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="pb-6">
                    <p class="text-sm text-stone-500 leading-relaxed">{{ $faq->answer_translations[app()->getLocale()] ?? $faq->answer_translations['fr'] ?? '' }}</p>
                </div>
            </div>
        @empty
            <p class="py-12 text-center text-stone-500">{{ __('faq.no_results') }}</p>
        @endforelse
    </div>
</div>
