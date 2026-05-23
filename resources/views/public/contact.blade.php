@extends('layouts.public')

@section('title', __('contact.page_title'))
@section('meta_description', __('contact.section_subtitle'))

@section('content')
    <x-public.page-hero
        :label="__('contact.section_title')"
        :title="__('contact.section_title')"
        :description="__('contact.section_subtitle')"
    />

    {{-- Two columns: info + form --}}
    <section class="pb-24 px-4 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            <x-public.contact-info-card class="lg:col-span-5" />

            {{-- Contact form --}}
            <div class="lg:col-span-7">
                @livewire('contact-form')
            </div>
        </div>
    </section>

    {{-- Decorative divider --}}
    <div class="flex items-center justify-center gap-3 pb-8">
        <span class="w-12 h-px bg-brass-300 block"></span>
        <svg class="size-3 text-brass-400" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        <span class="w-12 h-px bg-brass-300 block"></span>
    </div>

    <x-public.cta-section
        :title="__('home.cta_title')"
        :buttonText="__('home.cta_button')"
    />
@endsection
