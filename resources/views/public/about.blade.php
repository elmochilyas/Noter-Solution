@extends('layouts.public')

@section('title', __('about.page_title'))
@section('meta_description', __('about.hero_title'))

@section('content')
    <x-public.page-hero
        :label="__('about.section_label')"
        :title="__('about.hero_title')"
        :description="__('about.hero_desc')"
        accented
    />

    {{-- Decorative divider --}}
    <div class="flex items-center justify-center gap-3 pb-8">
        <span class="w-16 h-px bg-gradient-to-r from-transparent to-brass-300 block"></span>
        <svg class="size-3 text-brass-400" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        <span class="w-16 h-px bg-gradient-to-l from-transparent to-brass-300 block"></span>
    </div>

    {{-- Portrait + Bio --}}
    <section class="py-16 md:py-24 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-12 md:gap-16 items-start">
            <div class="md:col-span-5 reveal-left">
                <div class="relative rounded-2xl overflow-hidden border border-brass-200 bg-gradient-to-br from-brass-50 via-brass-100 to-brass-200 shadow-xl">
                    <div class="aspect-[3/4] flex items-center justify-center">
                        <div class="text-center">
                            <span class="font-serif text-[clamp(4rem,10vw,7rem)] font-semibold text-brass-500/50 select-none block leading-none">SB</span>
                            <span class="mt-4 block text-sm font-semibold uppercase tracking-widest text-brass-600/60">Notaire Adoul</span>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-brass-900/10 to-transparent pointer-events-none"></div>
                </div>
            </div>
            <div class="md:col-span-7 flex flex-col justify-center reveal-right">
                <h2 class="text-2xl md:text-3xl font-semibold text-ink mb-6">{{ __('about.bio_title') }}</h2>
                <div class="text-lg text-stone-500 space-y-6 mb-10 leading-relaxed">
                    <p>{{ __('about.bio_p1') }}</p>
                    <p>{{ __('about.bio_p2') }}</p>
                </div>
                <h2 class="text-2xl md:text-3xl font-semibold text-ink mb-6">{{ __('about.competence_title') }}</h2>
                @php
                    $competences = [
                        'users' => [
                            'key' => 'about.hero_title',
                            'path' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
                        ],
                        'home' => [
                            'key' => 'nav.services_realestate',
                            'path' => 'M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819',
                        ],
                        'landmark' => [
                            'key' => 'nav.services_business',
                            'path' => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z',
                        ],
                        'file-signature' => [
                            'key' => 'nav.services_contracts',
                            'path' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                        ],
                    ];
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($competences as $name => $comp)
                        <div class="glass-card flex items-center gap-4 p-4 group hover:border-brass-300 transition-fast">
                            <div class="size-11 shrink-0 rounded-xl bg-brass-100 border border-brass-200 flex items-center justify-center text-brass-600 group-hover:bg-brass-200 transition-fast">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $comp['path'] }}"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-ink">{{ __($comp['key']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="py-20 md:py-28 px-4 border-t border-stone-100 bg-gradient-to-b from-parchment/50 to-white">
        <div class="max-w-7xl mx-auto">
            <p class="section-label text-center mb-4 reveal-up">{{ __('about.credentials_title') }}</p>
            <h2 class="text-3xl md:text-4xl font-semibold text-ink text-center mb-16 reveal-up" style="animation-delay:60ms">{{ __('about.values_title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="card-premium p-8 reveal-up" style="animation-delay:{{ $i * 80 }}ms">
                        <div class="mb-6 size-12 rounded-2xl bg-brass-50 border border-brass-100 flex items-center justify-center text-brass-600 text-lg font-semibold font-serif">
                            {{ $i }}
                        </div>
                        <h3 class="text-xl font-semibold text-ink mb-3">{{ __('about.value_' . $i . '_title') }}</h3>
                        <p class="text-sm text-stone-500 leading-relaxed">{{ __('about.value_' . $i . '_desc') }}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- Credentials --}}
    <section class="py-16 md:py-20 px-4 border-t border-stone-100">
        <div class="max-w-7xl mx-auto">
            <h2 class="section-label text-center mb-10 reveal-up">{{ __('about.credentials_title') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-3xl mx-auto">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="glass-card flex items-center gap-4 p-5 reveal-up" style="animation-delay:{{ $i * 80 }}ms">
                        <div class="size-2.5 shrink-0 rounded-full bg-brass-500"></div>
                        <span class="text-base font-medium text-ink">{{ __('about.credential_' . $i) }}</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <x-public.cta-section
        :title="__('about.cta_title')"
        :buttonText="__('about.cta_button')"
    />
@endsection
