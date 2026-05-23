@extends('layouts.portal')

@section('title', __('portal.dashboard_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10 md:px-6 lg:px-8">

    {{-- Greeting --}}
    <div class="mb-10 reveal-up">
        <div class="flex items-start gap-4">
            {{-- Avatar --}}
            <div class="shrink-0 size-12 rounded-xl bg-gradient-to-br from-brass-100 to-brass-200 border border-brass-200 flex items-center justify-center shadow-sm">
                <span class="text-base font-bold text-brass-600" style="font-family: var(--font-display-fr);">
                    {{ strtoupper(substr(auth('client')->user()->full_name ?: 'C', 0, 1)) }}
                </span>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-ink leading-tight">
                    {{ __('portal.dashboard_greeting', ['name' => auth('client')->user()->full_name ?: __('portal.dear')]) }}
                </h1>
                <p class="text-stone-500 mt-1 text-sm">{{ __('portal.dashboard_subtitle') }}</p>
            </div>
        </div>
    </div>

    {{-- Upcoming booking --}}
    @php
        $upcoming = auth('client')->user()->bookings()
            ->whereIn('status', ['pending_payment', 'confirmed'])
            ->where('starts_at', '>=', now())
            ->with(['plan'])
            ->orderBy('starts_at')
            ->first();
    @endphp

    @if ($upcoming)
        <div class="mb-8 relative overflow-hidden rounded-2xl border border-brass-200/60 bg-gradient-to-br from-brass-50 to-white p-6 shadow-sm reveal-up" style="animation-delay:80ms">
            {{-- Accent bar --}}
            <div class="absolute top-0 start-0 end-0 h-[3px] bg-gradient-to-r from-brass-300 via-brass-500 to-brass-300"></div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                <div class="space-y-1.5">
                    <p class="text-[0.65rem] font-bold uppercase tracking-widest text-brass-500">
                        {{ __('portal.next_booking') }}
                    </p>
                    <p class="text-lg font-semibold text-ink">
                        {{ locale_string($upcoming->plan?->name_translations ?? [], app()->getLocale()) ?: ($upcoming->plan?->name ?? '-') }}
                    </p>
                    <div class="flex flex-wrap gap-3 items-center">
                        <p class="text-sm text-stone-500">
                            {{ $upcoming->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                        </p>
                        <span class="chip text-[0.7rem]">{{ __('booking.status.'.$upcoming->status) }}</span>
                        <span class="text-xs text-stone-400 font-mono">{{ $upcoming->reference }}</span>
                    </div>
                </div>
                <div class="shrink-0">
                    <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $upcoming->reference }}"
                       class="btn-brass text-sm px-5 py-2.5 rounded-xl scale-on-press">
                        {{ __('portal.view') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="mb-8 rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm reveal-up" style="animation-delay:80ms">
            <div class="mx-auto size-14 rounded-xl bg-stone-50 border border-stone-100 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </div>
            <p class="text-stone-500 text-sm mb-5">{{ __('portal.dashboard_empty') }}</p>
            <a href="/{{ app()->getLocale() }}/book"
               class="btn-brass text-sm px-6 py-2.5 rounded-xl scale-on-press">
                {{ __('portal.nav_book_appointment') }}
            </a>
        </div>
    @endif

    {{-- Quick access cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 reveal-up" style="animation-delay:160ms">
        @foreach ([
            ['href' => '/portal/bookings',   'key' => 'portal.nav_bookings',   'sub' => 'portal.see_all_bookings',   'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
            ['href' => '/portal/receipts',   'key' => 'portal.nav_receipts',   'sub' => 'portal.download_receipts',  'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
            ['href' => '/portal/preferences','key' => 'portal.nav_preferences','sub' => 'portal.manage_preferences', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ] as $card)
            <a href="/{{ app()->getLocale() }}{{ $card['href'] }}"
               class="group card-premium p-6 flex flex-col gap-4 text-center hover:border-brass-200">
                <div class="mx-auto size-11 rounded-xl bg-brass-50 border border-brass-100 flex items-center justify-center group-hover:bg-brass-100 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-semibold text-ink">{{ __($card['key']) }}</p>
                    <p class="text-sm text-stone-500 mt-0.5">{{ __($card['sub']) }}</p>
                </div>
                <div class="mt-auto flex items-center justify-center gap-1 text-xs font-semibold text-brass-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    Ouvrir
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endsection
