@php
    $locale = app()->getLocale();
@endphp

<div x-data="{ show: !localStorage.getItem('cookie_consent') }" x-show="show" x-cloak class="fixed bottom-0 inset-x-0 z-50 bg-ink text-parchment p-4 md:p-6 shadow-lg">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6">
        <p class="text-sm text-stone-300 leading-relaxed flex-1">
            {{ $locale === 'ar' ? 'يستخدم هذا الموقع ملفات تعريف الارتباط الضرورية فقط لضمان حسن سيره. لمزيد من المعلومات، راجع' : 'Ce site utilise uniquement des cookies strictement nécessaires à son fonctionnement. Pour plus d\'informations, consultez notre' }}
            <a href="/{{ $locale }}/politique-confidentialite" class="text-brass-400 hover:text-brass-300 underline underline-offset-2 transition-fast">
                {{ $locale === 'ar' ? 'سياسة الخصوصية' : 'politique de confidentialité' }}
            </a>.
        </p>
        <div class="flex items-center gap-3 shrink-0">
            <button x-on:click="localStorage.setItem('cookie_consent', '1'); show = false" class="rounded-md bg-brass-500 px-5 py-2 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ $locale === 'ar' ? 'موافق' : 'Accepter' }}
            </button>
        </div>
    </div>
</div>
