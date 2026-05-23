@props([
    'title',
    'description' => null,
    'buttonText',
    'buttonUrl' => '/' . app()->getLocale() . '/book',
    'accented' => false,
])

<section {{ $attributes->merge(['class' => 'relative overflow-hidden py-28 md:py-36 px-4']) }}>
    {{-- Dark background --}}
    <div class="absolute inset-0 bg-ink"></div>

    {{-- Decorative gradient blobs --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-brass-500/50 to-transparent"></div>
        <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-brass-500/50 to-transparent"></div>
        @if ($accented)
            <div class="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] rounded-full bg-brass-500/8 blur-3xl breathe"></div>
            <div class="absolute top-0 start-0 w-[300px] h-[300px] rounded-full bg-brass-600/5 blur-3xl float-slow"></div>
            <div class="absolute bottom-0 end-0 w-[250px] h-[250px] rounded-full bg-brass-400/5 blur-3xl float-delayed"></div>
        @endif
    </div>

    {{-- Subtle grid overlay --}}
    <div class="pointer-events-none absolute inset-0 opacity-[0.025]"
         style="background-image: linear-gradient(rgba(182,138,62,0.4) 1px, transparent 1px), linear-gradient(90deg, rgba(182,138,62,0.4) 1px, transparent 1px); background-size: 56px 56px;"></div>

    <div class="relative max-w-4xl mx-auto text-center flex flex-col items-center gap-10">
        {{-- Label --}}
        <div class="inline-flex items-center gap-3 text-[0.65rem] font-bold uppercase tracking-[0.2em] text-brass-400">
            <div class="h-px w-8 bg-brass-600"></div>
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            <div class="h-px w-8 bg-brass-600"></div>
        </div>

        <h2 class="text-4xl md:text-5xl lg:text-[3.25rem] font-semibold text-parchment leading-[1.1] max-w-3xl">
            {{ $title }}
        </h2>

        @if ($description)
            <p class="text-lg text-stone-400 max-w-xl leading-relaxed">
                {{ $description }}
            </p>
        @endif

        <a href="{{ $buttonUrl }}"
           class="btn-brass h-14 px-12 text-[0.9375rem] rounded-xl scale-on-press glow-pulse">
            {{ $buttonText }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
        </a>

        {{-- Contact alternative --}}
        <p class="text-sm text-stone-500">
            {{ __('footer.or') ?? 'ou' }}
            <a href="tel:+212528380719" class="text-brass-400 hover:text-brass-300 font-medium transition-fast">
                <bdi dir="ltr">05 28 38 07 19</bdi>
            </a>
        </p>
    </div>
</section>
