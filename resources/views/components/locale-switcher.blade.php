@php
    $current = app()->getLocale();
    $other = $current === 'ar' ? 'fr' : 'ar';
    $path = request()->route() ? str_replace("/{$current}", "/{$other}", request()->getRequestUri()) : "/{$other}";
@endphp

<a href="{{ $path }}"
   class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200/80 bg-white/60 px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-stone-600 hover:bg-stone-100 hover:border-stone-300 hover:text-ink transition-fast shadow-sm"
   aria-label="{{ $other === 'ar' ? __('common.arabic') : __('common.french') }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-brass-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>
    </svg>
    <span>{{ $other === 'ar' ? 'AR' : 'FR' }}</span>
</a>
