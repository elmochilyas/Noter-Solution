@extends('layouts.portal')

@section('title', __('portal.receipts_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-8">{{ __('portal.receipts_title') }}</h1>

    @forelse ($receipts as $receipt)
        <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-5 mb-3">
            <div>
                <p class="font-medium text-ink">{{ $receipt->number }}</p>
                <p class="text-sm text-stone-500 mt-0.5">
                    {{ $receipt->issued_at->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}
                    · {{ number_format($receipt->amount_centimes / 100, 2, ',', ' ') }} MAD
                    · {{ $receipt->booking?->reference }}
                </p>
            </div>
            <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $receipt->booking?->reference }}/receipt/{{ $receipt->id }}"
               class="rounded-md bg-brass-500 px-4 py-2 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                {{ __('portal.download') }}
            </a>
        </div>
    @empty
        <div class="rounded-xl border border-stone-200 bg-white p-8 text-center">
            <p class="text-stone-500">{{ __('portal.no_receipts') }}</p>
        </div>
    @endforelse
</section>
@endsection
