@extends('layouts.portal')

@section('title', __('portal.receipts_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-10 md:px-6 lg:px-8">
    <div class="mb-8 reveal-up">
        <h1 class="text-2xl md:text-3xl font-semibold text-ink">{{ __('portal.receipts_title') }}</h1>
    </div>

    <div class="space-y-3 reveal-up" style="animation-delay:60ms">
        @forelse ($receipts as $receipt)
            <div class="card-premium p-5 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-semibold text-ink truncate">{{ $receipt->number }}</p>
                    <p class="text-sm text-stone-500 mt-0.5">
                        {{ $receipt->issued_at->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}
                        <span class="mx-1.5 text-stone-300">·</span>
                        <span class="font-medium text-ink">{{ number_format($receipt->amount_centimes / 100, 2, ',', ' ') }} {{ __('portal.currency') }}</span>
                        <span class="mx-1.5 text-stone-300">·</span>
                        <span class="font-mono text-xs">{{ $receipt->booking?->reference }}</span>
                    </p>
                </div>
                <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $receipt->booking?->reference }}/receipt/{{ $receipt->id }}"
                   class="btn-brass shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm scale-on-press">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    {{ __('portal.download') }}
                </a>
            </div>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto size-14 rounded-xl bg-stone-50 border border-stone-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9l1.5 1.5m0 0l1.5-1.5m-1.5 1.5V21M12 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <p class="text-stone-500 text-sm">{{ __('portal.no_receipts') }}</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
