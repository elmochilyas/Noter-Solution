@extends('layouts.public')

@section('title', __('contact.page_title'))
@section('meta_description', __('contact.section_subtitle'))

@section('content')
    {{-- Page intro --}}
    <section class="pt-32 pb-16 px-4 max-w-7xl mx-auto text-center">
        <span class="text-sm font-semibold uppercase tracking-widest text-brass-500 mb-6 block">{{ __('contact.section_title') }}</span>
        <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-6 max-w-3xl mx-auto leading-tight" style="font-family: var(--font-display-fr);">
            {{ __('contact.section_title') }}
        </h1>
        <p class="text-lg text-stone-500 max-w-2xl mx-auto leading-relaxed">
            {{ __('contact.section_subtitle') }}
        </p>
    </section>

    {{-- Two columns: info + form --}}
    <section class="pb-24 px-4 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            {{-- Contact info --}}
            <div class="lg:col-span-5 space-y-8">
                @php $info = \App\Models\Setting::practiceInfo(); @endphp
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __('contact.address_title') }}</h2>
                    <p class="text-base text-ink">{{ $info['address'] ?: 'Bensergao, près du Tribunal de Première Instance, Agadir, Maroc' }}</p>
                    <div class="mt-3 rounded-lg overflow-hidden border border-stone-200 bg-stone-100">
                        <img src="https://via.placeholder.com/400x200/EFEAE0/6B6660?text=Carte" alt="Plan d'accès au cabinet" class="w-full h-auto" loading="lazy" width="400" height="200">
                    </div>
                    <a href="https://www.openstreetmap.org/search?query={{ urlencode($info['address'] ?: 'Bensergao Agadir Maroc') }}" target="_blank" rel="noopener noreferrer" class="mt-2 text-sm font-medium text-brass-500 hover:text-brass-600 inline-flex items-center gap-1 transition-fast">
                        {{ __('contact.directions_title') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __('contact.hours_title') }}</h2>
                    <p class="text-base text-ink">{{ app()->getLocale() === 'ar' ? $info['hours_ar'] : $info['hours_fr'] }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __('contact.phone_title') }}</h2>
                    <div class="space-y-2">
                        <a href="tel:{{ $info['phone'] ?: '+212528380719' }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            <bdi dir="ltr">{{ $info['phone'] ?: '05 28 38 07 19' }}</bdi>
                        </a>
                        @if(!empty($info['mobile']))
                            <a href="tel:{{ $info['mobile'] }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                                <bdi dir="ltr">{{ $info['mobile'] }}</bdi>
                            </a>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __('contact.email_title') }}</h2>
                    <a href="mailto:{{ $info['email'] ?: 'sana.bouhamidi@gmail.com' }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        <bdi dir="ltr">{{ $info['email'] ?: 'sana.bouhamidi@gmail.com' }}</bdi>
                    </a>
                </div>
            </div>

            {{-- Contact form --}}
            <div class="lg:col-span-7">
                @livewire('contact-form')
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 bg-[#0E1B2C] px-4">
        <div class="max-w-3xl mx-auto text-center flex flex-col items-center gap-8">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment">{{ __('home.cta_title') }}</h2>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('home.cta_button') }}
            </a>
        </div>
    </section>
@endsection
