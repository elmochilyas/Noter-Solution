@extends('layouts.public')

@section('title', __('about.page_title'))
@section('meta_description', __('about.hero_title'))

@section('content')
    {{-- Page intro --}}
    <section class="pt-24 md:pt-32 pb-24 px-4 max-w-7xl mx-auto">
        <div class="flex flex-col items-center text-center">
            <span class="text-sm font-semibold uppercase tracking-[0.1em] text-brass-500 mb-6 inline-flex items-center gap-4">
                <span class="w-8 h-px bg-brass-500 block"></span>
                {{ __('about.section_label') }}
                <span class="w-8 h-px bg-brass-500 block"></span>
            </span>
            <h1 class="text-4xl md:text-5xl font-semibold text-ink max-w-4xl mb-8" style="font-family: var(--font-display-fr);">
                {{ __('about.hero_title') }}
            </h1>
            <p class="text-lg text-stone-500 max-w-[720px] leading-relaxed">
                {{ __('about.hero_desc') }}
            </p>
        </div>
    </section>

    {{-- Portrait + Bio --}}
    <section class="py-24 px-4 border-t border-stone-200 bg-white">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-12 items-start">
            <div class="md:col-span-5">
                <div class="rounded-lg overflow-hidden border border-stone-200 aspect-[3/4] bg-stone-100">
                    <img src="https://via.placeholder.com/600x800/EFEAE0/6B6660?text=Sana+Bouhamidi" alt="Portrait de Maître Sana Bouhamidi" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="md:col-span-7 flex flex-col justify-center">
                <h2 class="text-2xl font-semibold text-ink mb-6">{{ __('about.bio_title') }}</h2>
                <div class="text-base text-stone-500 space-y-6 mb-12 leading-relaxed">
                    <p>{{ __('about.bio_p1') }}</p>
                    <p>{{ __('about.bio_p2') }}</p>
                </div>
                <h2 class="text-2xl font-semibold text-ink mb-6">{{ __('about.competence_title') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach (['users' => 'about.hero_title', 'home' => 'nav.services_realestate', 'landmark' => 'nav.services_business', 'file-signature' => 'nav.services_contracts'] as $icon => $key)
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-full border border-brass-500 flex items-center justify-center text-brass-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </div>
                            <span class="text-sm font-medium text-ink">{{ __($key) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="py-24 px-4 border-t border-stone-200">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-semibold text-ink text-center mb-16">{{ __('about.values_title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="bg-white p-8 border border-stone-200 rounded-lg relative overflow-hidden group hover:border-brass-500 transition-colors duration-300">
                        <div class="absolute top-0 start-0 w-full h-[3px] bg-brass-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <h3 class="text-xl font-semibold text-ink mb-4">{{ __('about.value_' . $i . '_title') }}</h3>
                        <p class="text-sm text-stone-500 leading-relaxed">{{ __('about.value_' . $i . '_desc') }}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- Credentials --}}
    <section class="py-16 bg-white px-4 border-t border-stone-200">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-sm font-semibold uppercase tracking-[0.1em] text-brass-500 mb-8">{{ __('about.credentials_title') }}</h2>
            <div class="flex flex-wrap justify-center items-center gap-x-12 gap-y-6">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="flex items-center gap-3">
                        <div class="size-2 rounded-full bg-brass-500"></div>
                        <span class="text-lg text-ink">{{ __('about.credential_' . $i) }}</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 bg-[#0E1B2C] px-4">
        <div class="max-w-7xl mx-auto text-center flex flex-col items-center">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment mb-8 max-w-2xl">{{ __('about.cta_title') }}</h2>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('about.cta_button') }}
            </a>
        </div>
    </section>
@endsection
