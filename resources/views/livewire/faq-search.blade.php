<div>
    {{-- Search --}}
    <div class="relative mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute start-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        <input type="search" wire:model.live.debounce.300ms="query" placeholder="{{ __('faq.search_placeholder') }}" class="block h-11 w-full rounded-xl border border-stone-200 bg-white ps-12 pe-4 text-sm text-ink placeholder:text-stone-400 focus:border-brass-400 focus:outline-none focus:ring-2 focus:ring-brass-400/20 transition-fast shadow-sm">
    </div>

    {{-- Category filters --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <button wire:click="clearCategory" class="rounded-full border px-4 py-1.5 text-xs font-semibold transition-fast @if(!$category) bg-brass-500 text-parchment border-brass-500 shadow-sm @else bg-white text-stone-600 border-stone-200 hover:border-brass-200 hover:bg-brass-50/50 @endif">
            {{ __('faq.all_categories') }}
        </button>
        @foreach ($categories as $cat)
            <button wire:click="filterByCategory('{{ $cat }}')" class="rounded-full border px-4 py-1.5 text-xs font-semibold transition-fast @if($category === $cat) bg-brass-500 text-parchment border-brass-500 shadow-sm @else bg-white text-stone-600 border-stone-200 hover:border-brass-200 hover:bg-brass-50/50 @endif">
                {{ __("faq.category_{$cat}") }}
            </button>
        @endforeach
    </div>

    {{-- FAQ list --}}
    <div class="divide-y divide-stone-100">
        @forelse ($faqs as $faq)
            <div x-data="{ open: false }" class="group">
                <button x-on:click="open = !open; $wire.incrementViewCount({{ $faq->id }})" class="w-full py-6 flex justify-between items-center text-start" :aria-expanded="open">
                    <span class="text-base font-medium text-ink group-hover:text-brass-600 transition-fast pe-6">{{ locale_string($faq->question_translations, app()->getLocale()) }}</span>
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
                    <p class="text-sm text-stone-500 leading-relaxed">{{ locale_string($faq->answer_translations, app()->getLocale()) }}</p>
                </div>
            </div>
        @empty
            <p class="py-16 text-center text-stone-400 text-sm">{{ __('faq.no_results') }}</p>
        @endforelse
    </div>
</div>
