@props([
    'info' => \App\Models\Setting::practiceInfo(),
    'wrapped' => false,
    'showMap' => true,
    'keyPrefix' => 'contact',
])

@php
    $sectionClasses = $wrapped ? 'bg-white p-6 rounded-xl border border-stone-100 shadow-sm' : '';
@endphp

<div {{ $attributes->merge(['class' => 'space-y-8']) }}>
    {{-- Address --}}
    <div @class([$sectionClasses])>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __($keyPrefix . '.address_title') }}</h2>
        <p class="text-base text-ink mb-4">{{ $info['address'] ?: 'Bensergao, près du Tribunal de Première Instance, Agadir, Maroc' }}</p>
        @if ($showMap)
            <div class="rounded-lg overflow-hidden border border-stone-200">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=-9.615%2C30.395%2C-9.585%2C30.415&amp;layer=mapnik&amp;marker=30.405%2C-9.600"
                    width="100%"
                    height="220"
                    style="border:0; display: block;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Carte du cabinet"
                ></iframe>
            </div>
            <a href="https://www.openstreetmap.org/search?query={{ urlencode($info['address'] ?: 'Bensergao Agadir Maroc') }}" target="_blank" rel="noopener noreferrer" class="mt-2 text-sm font-medium text-brass-500 hover:text-brass-600 inline-flex items-center gap-1 transition-fast">
                {{ __($keyPrefix . '.directions_title') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        @endif
    </div>

    {{-- Hours --}}
    <div @class([$sectionClasses])>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __($keyPrefix . '.hours_title') }}</h2>
        <p class="text-base text-ink">{{ app()->getLocale() === 'ar' ? $info['hours_ar'] : $info['hours_fr'] }}</p>
    </div>

    {{-- Phone --}}
    <div @class([$sectionClasses])>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __($keyPrefix . '.phone_title') }}</h2>
        <a href="tel:{{ $info['phone'] ?: '+212528380719' }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
            <bdi dir="ltr">{{ $info['phone'] ?: '05 28 38 07 19' }}</bdi>
        </a>
        @if(!empty($info['mobile']))
            <a href="tel:{{ $info['mobile'] }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                <bdi dir="ltr">{{ $info['mobile'] }}</bdi>
            </a>
        @endif
    </div>

    {{-- Email --}}
    <div @class([$sectionClasses])>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-brass-500 mb-4">{{ __($keyPrefix . '.email_title') }}</h2>
        <a href="mailto:{{ $info['email'] ?: 'sana.bouhamidi@gmail.com' }}" class="flex items-center gap-3 text-base text-ink hover:text-brass-500 transition-fast">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            <bdi dir="ltr">{{ $info['email'] ?: 'sana.bouhamidi@gmail.com' }}</bdi>
        </a>
    </div>
</div>
