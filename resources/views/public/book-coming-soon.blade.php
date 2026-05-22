@extends('layouts.public')

@section('title', __('common.coming_soon') . ' — Sana Bouhamidi')
@section('meta_description', __('common.coming_soon_desc'))

@section('content')
    <section class="flex-1 flex items-center justify-center px-4 py-32">
        <div class="max-w-lg mx-auto text-center">
            <div class="size-20 rounded-full bg-brass-500/10 border border-brass-500/20 flex items-center justify-center mx-auto mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-semibold text-ink mb-6" style="font-family: var(--font-display-fr);">
                {{ __('common.coming_soon') }}
            </h1>
            <p class="text-lg text-stone-500 leading-relaxed mb-10">
                {{ __('common.coming_soon_desc') }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="tel:+212528380719" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast w-full sm:w-auto">
                    {{ __('common.call') }}
                </a>
                <a href="https://wa.me/212666120661" target="_blank" rel="noopener noreferrer" class="inline-flex h-12 items-center justify-center rounded-md border border-ink px-8 text-sm font-semibold text-ink hover:bg-ink hover:text-parchment transition-fast w-full sm:w-auto">
                    {{ __('common.whatsapp') }}
                </a>
            </div>
        </div>
    </section>
@endsection
