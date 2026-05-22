@extends('layouts.portal')

@section('title', __('portal.dashboard_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-4xl px-4 py-16 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-8">
        {{ __('portal.dashboard_title') }}
    </h1>
    <p class="text-stone-500">
        {{ __('portal.dashboard_empty') }}
    </p>
</section>
@endsection
