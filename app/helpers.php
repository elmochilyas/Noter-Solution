<?php

if (! function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        $nonce = view()->shared('cspNonce')
            ?? request()->attributes->get('csp_nonce')
            ?? '';

        return $nonce;
    }
}

if (! function_exists('locale_string')) {
    function locale_string(array $translations, string $locale, string $fallback = 'fr', string $default = ''): string
    {
        $value = $translations[$locale] ?? $translations[$fallback] ?? $default;

        if (is_array($value)) {
            return $default;
        }

        return (string) $value;
    }
}
