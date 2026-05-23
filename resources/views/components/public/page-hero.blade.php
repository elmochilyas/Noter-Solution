@props([
    'label',
    'title',
    'description' => null,
    'accented' => false,
])

@if ($accented)
    <section {{ $attributes->merge(['class' => 'relative overflow-hidden pt-24 md:pt-32 pb-16 md:pb-24 px-4']) }}>
        {{-- Background elements --}}
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-16 end-0 w-[500px] h-[500px] rounded-full bg-brass-100/45 blur-3xl float-slow"></div>
            <div class="absolute bottom-0 start-0 w-[300px] h-[300px] rounded-full bg-brass-200/30 blur-3xl float-delayed"></div>
            <div class="absolute inset-0 opacity-[0.014]"
                 style="background-image: linear-gradient(#B68A3E 1px, transparent 1px), linear-gradient(90deg, #B68A3E 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <div class="max-w-7xl mx-auto relative">
            <div class="flex flex-col items-center text-center">
                <div class="section-label mb-6 reveal-up">
                    {{ $label }}
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold text-ink max-w-4xl mb-7 leading-[1.08] tracking-tight reveal-up" style="animation-delay:80ms">
                    {{ $title }}
                </h1>
                @if ($description)
                    <p class="text-xl text-stone-500 max-w-2xl leading-relaxed reveal-up" style="animation-delay:160ms">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Bottom fade --}}
        <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-parchment/60 to-transparent pointer-events-none"></div>
    </section>
@else
    <section {{ $attributes->merge(['class' => 'pt-28 md:pt-36 pb-14 md:pb-20 px-4']) }}>
        <div class="max-w-7xl mx-auto text-center">
            <div class="section-label mb-5 justify-center reveal-up">{{ $label }}</div>
            <h1 class="text-4xl md:text-5xl lg:text-[3.25rem] font-semibold text-ink mb-6 max-w-3xl mx-auto leading-[1.1] tracking-tight reveal-up" style="animation-delay:80ms">
                {{ $title }}
            </h1>
            @if ($description)
                <p class="text-xl text-stone-500 max-w-2xl mx-auto leading-relaxed reveal-up" style="animation-delay:160ms">
                    {{ $description }}
                </p>
            @endif
        </div>
    </section>
@endif
