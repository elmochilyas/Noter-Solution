@extends('layouts.public')

@section('title', __('office.page_title'))
@section('meta_description', __('office.section_subtitle'))

@section('content')
    <x-public.page-hero
        :label="__('office.section_label')"
        :title="__('office.section_title')"
        :description="__('office.section_subtitle')"
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

    {{-- Office info --}}
    <section class="py-16 md:py-24 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            <x-public.contact-info-card :wrapped="true" key-prefix="office" class="lg:col-span-5 reveal-left" />

            {{-- Right: presentation + services --}}
            <div class="lg:col-span-7 space-y-10 reveal-right">
                <div>
                    <h2 class="text-2xl md:text-3xl font-semibold text-ink mb-6">{{ __('office.presentation_title') }}</h2>
                    <div class="text-base text-stone-500 space-y-4 leading-relaxed">
                        <p>{{ __('office.presentation_desc') }}</p>
                        <p>{{ __('office.presentation_atmosphere') }}</p>
                    </div>
                </div>

                <div class="glass-card p-8">
                    <h3 class="text-lg font-semibold text-ink mb-6">{{ __('office.services_title') }}</h3>
                    <ul class="space-y-4">
                        @foreach (__('office.services_list') as $service)
                            <li class="flex items-start gap-3 text-base text-stone-600">
                                <div class="size-6 rounded-full bg-brass-50 border border-brass-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="h-3.5 w-3.5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span>{{ $service }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <x-public.cta-section
        :title="__('office.cta_title')"
        :buttonText="__('office.cta_button')"
        accented
    />
@endsection
