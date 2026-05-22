@extends('layouts.public')

@section('title', 'Sana Bouhamidi — Notaire Adoul à Agadir')
@section('meta_description', __('common.tagline'))

@php $practiceInfo = \App\Models\Setting::practiceInfo(); @endphp

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": ["LegalService", "LocalBusiness"],
  "@id": "{{ url('/') }}",
  "name": "Sana Bouhamidi — Notaire Adoul",
  "description": "{{ __('common.tagline') }}",
  "url": "{{ url('/' . app()->getLocale()) }}",
  "telephone": "{{ $practiceInfo['phone'] ?: '+212528380719' }}",
  "email": "{{ $practiceInfo['email'] ?: 'sana.bouhamidi@gmail.com' }}",
  "image": "{{ url('/images/sana-portrait.jpg') }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ $practiceInfo['address'] ?: 'Bensergao, près du Tribunal de Première Instance' }}",
    "addressLocality": "Agadir",
    "addressCountry": "MA"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "09:00",
      "closes": "17:00"
    }
  ],
  "sameAs": [
    "https://wa.me/{{ $practiceInfo['whatsapp'] ?: '212666120661' }}"
  ]
}
</script>
@endsection

@section('content')
    {{-- Hero --}}
    <section class="pt-[120px] pb-24 md:pb-32 px-4 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center gap-12 lg:gap-24">
            <div class="w-full md:w-[60%] flex flex-col gap-6">
                <span class="text-sm font-semibold uppercase tracking-widest text-brass-500">
                    {{ __('home.hero_label') }}
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold text-ink leading-tight text-balance" style="font-family: var(--font-display-fr);">
                    {{ __('home.hero_title') }}
                </h1>
                <p class="text-lg text-stone-500 max-w-2xl leading-relaxed">
                    {{ __('home.hero_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 mt-4">
                    <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                        {{ __('home.hero_cta_booking') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/cabinet" class="inline-flex h-12 items-center justify-center rounded-md border border-ink px-8 text-sm font-semibold text-ink hover:bg-ink hover:text-parchment transition-fast">
                        {{ __('home.hero_cta_chat') }}
                    </a>
                </div>
            </div>
            <div class="w-full md:w-[40%] relative">
                <div class="relative rounded-lg border border-stone-200 overflow-hidden aspect-[3/4] bg-stone-100">
                    <img src="https://via.placeholder.com/600x800/EFEAE0/6B6660?text=Sana+Bouhamidi" alt="Portrait de Maître Sana Bouhamidi" class="absolute inset-0 w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-6 -start-6 w-16 h-16 md:w-24 md:h-24 rounded-full border border-stone-200 bg-parchment hidden md:flex items-center justify-center" aria-hidden="true">
                    <img src="/images/ornaments/divider.svg" alt="" class="w-12" role="presentation">
                </div>
            </div>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-y border-stone-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-0 lg:divide-x divide-stone-200">
                @foreach (['school', 'workspace_premium', 'location_on', 'lock'] as $i => $icon)
                    <div class="flex items-center gap-4 lg:px-6 first:ps-0 last:pe-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0-.419 1.123M4.26 10.147c.017.075.036.15.057.224m0 0 .133.5M4.26 10.147l-1.02.204a1.125 1.125 0 0 0-.504 1.874l1.222 1.222c.215.215.34.508.34.814v.054c0 .364.184.703.498.881l.464.264a1.125 1.125 0 0 1 .69 1.013v.363a1.125 1.125 0 0 0 1.124 1.125h.963a1.125 1.125 0 0 1 .999.614l.344.688a1.124 1.124 0 0 0 1.254.57l.18-.045A1.125 1.125 0 0 0 12 19.311v-.54a1.125 1.125 0 0 1 .472-.899l5.007-2.78a1.5 1.5 0 0 0 .771-1.328v-5.132a1.5 1.5 0 0 0-.707-1.28l-5.636-3.593a1.125 1.125 0 0 0-1.214 0l-5.637 3.593a1.5 1.5 0 0 0-.56 1.2z"/>
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wider text-stone-700">@lang('home.trust_' . ($i + 1) . '_label')</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Services overview --}}
    <section class="py-24 md:py-32 px-4 max-w-7xl mx-auto" id="services">
        <div class="flex flex-col gap-12">
            <div class="text-center md:text-start flex flex-col gap-4">
                <span class="text-sm font-semibold uppercase tracking-widest text-brass-500">{{ __('nav.services') }}</span>
                <h2 class="text-3xl md:text-4xl font-semibold text-ink">{{ __('home.services_title') }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                @forelse ($services as $service)
                    <div class="bg-white border border-stone-200 rounded-lg p-6 md:p-8 relative overflow-hidden group hover:shadow-sm transition-shadow">
                        <div class="absolute top-0 start-0 end-0 h-[3px] bg-brass-500"></div>
                        <div class="flex flex-col h-full gap-4">
                            <h3 class="text-xl font-semibold text-ink">{{ $service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '' }}</h3>
                            <p class="text-base text-stone-500 line-clamp-3">{{ $service->intro_translations[app()->getLocale()] ?? $service->intro_translations['fr'] ?? '' }}</p>
                            <a href="/{{ app()->getLocale() }}/services/{{ $service->slug }}" class="mt-auto text-sm font-semibold text-brass-500 hover:text-brass-600 inline-flex items-center gap-1 w-max transition-fast">
                                {{ __('home.services_link') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-500 col-span-2">{{ __('common.empty') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Process section --}}
    <section class="py-24 md:py-32 px-4 max-w-7xl mx-auto border-t border-stone-200">
        <h2 class="text-3xl md:text-4xl font-semibold text-ink text-center mb-16">{{ __('home.process_title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @for ($i = 1; $i <= 4; $i++)
                <div class="flex flex-col gap-4 items-center text-center">
                    <span class="text-5xl font-semibold text-brass-500 opacity-80">0{{ $i }}</span>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-ink border-b border-stone-200 pb-2">{{ __('home.process_step' . $i) }}</h3>
                    <p class="text-sm text-stone-500">{{ __('home.process_step' . $i . '_desc') }}</p>
                </div>
            @endfor
        </div>
    </section>

    {{-- Values section --}}
    <section class="py-24 md:py-32 px-4 max-w-7xl mx-auto border-t border-stone-200">
        <h2 class="text-3xl md:text-4xl font-semibold text-ink text-center mb-16">{{ __('home.values_title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @for ($i = 1; $i <= 4; $i++)
                <div class="flex flex-col gap-4">
                    <span class="text-5xl font-semibold text-brass-500 opacity-80">0{{ $i }}</span>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-ink border-b border-stone-200 pb-2">{{ __('home.value_' . $i . '_title') }}</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">{{ __('home.value_' . $i . '_desc') }}</p>
                </div>
            @endfor
        </div>
    </section>

    {{-- About teaser --}}
    <section class="py-16 px-4 max-w-7xl mx-auto border-t border-stone-200">
        <div class="flex flex-col md:flex-row gap-8 items-center">
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-full border border-stone-200 overflow-hidden shrink-0 bg-stone-100">
                <img src="https://via.placeholder.com/128x128/EFEAE0/6B6660?text=SB" alt="" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="text-xl font-semibold text-ink mb-2">{{ __('home.about_teaser_title') }}</h2>
                <p class="text-sm text-stone-500 max-w-prose">{{ __('home.about_teaser_desc') }}</p>
                <a href="/{{ app()->getLocale() }}/maitre-bouhamidi" class="mt-3 text-sm font-semibold text-brass-500 hover:text-brass-600 inline-flex items-center gap-1 transition-fast">
                    {{ __('home.about_teaser_link') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- FAQ teaser --}}
    <section class="py-24 md:py-32 px-4 max-w-3xl mx-auto" id="faq">
        <h2 class="text-3xl md:text-4xl font-semibold text-ink text-center mb-12">{{ __('home.faq_teaser_title') }}</h2>
        <div class="border-t border-stone-200">
            @forelse ($faqs as $faq)
                <div x-data="faqItem()" class="border-b border-stone-200">
                    <button x-on:click="toggle()" class="w-full py-6 flex justify-between items-center text-start group" aria-expanded="false" :aria-expanded="open">
                        <span class="text-lg font-medium text-ink">{{ $faq->question_translations[app()->getLocale()] ?? $faq->question_translations['fr'] ?? '' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="pb-6">
                        <p class="text-sm text-stone-500 leading-relaxed">{{ $faq->answer_translations[app()->getLocale()] ?? $faq->answer_translations['fr'] ?? '' }}</p>
                    </div>
                </div>
            @empty
                <p class="py-6 text-stone-500">{{ __('common.empty') }}</p>
            @endforelse
        </div>
        <div class="mt-8 text-center">
            <a href="/{{ app()->getLocale() }}/faq" class="text-sm font-semibold text-brass-500 hover:text-brass-600 inline-flex items-center gap-1 transition-fast">
                {{ __('home.faq_teaser_link') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-[#0E1B2C] py-20 px-4">
        <div class="max-w-3xl mx-auto text-center flex flex-col items-center gap-8">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment">{{ __('home.cta_title') }}</h2>
            <p class="text-lg text-stone-300 max-w-xl">{{ __('home.cta_desc') }}</p>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast w-full sm:w-auto">
                {{ __('home.cta_button') }}
            </a>
        </div>
    </section>
@endsection
