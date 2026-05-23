<?php

namespace App\Domain\Services\Chatbot;

use App\Models\ConsultationPlan;

final class OutputFilter
{
    private const FORBIDDEN_PATTERNS = [
        // Legal advice phrasing
        '/\b(?:je suis un? avocat|i am a lawyer|أنا محام|je suis conseil|legal counsel)\b/i',
        '/\b(?:conseil juridique|legal advice|استشارة قانونية|conseil légal)\b/i',

        // Superlatives (FR)
        '/\b(?:le meilleur?|la meilleure?|les meilleur?es?|le plus rapide|la plus rapide|les plus rapides|le plus expérimenté|le plus compétent)\b/i',

        // Superlatives (AR)
        '/\b(?:الأفضل|الأسرع|الأكثر خبرة|الأكفأ|الأميز)\b/u',

        // Guarantees / promises
        '/\b(?:garanti|garantie|guaranteed|مضمون|مضمونة|garantissons)\b/i',

        // Authentication act fees — hard reject (amounts NOT in consultation plans)
        // This is checked dynamically in violationCount against allowed prices
    ];

    private const ESCALATION_THRESHOLD = 2;

    private const AMOUNT_PATTERN = '/\b(\d{1,4})\s?(DH|MAD|د\.م\.|درهم)\b/i';

    public function filter(string $response, array $allowedAmounts = []): string
    {
        $violations = $this->violationCount($response, null, $allowedAmounts);

        if ($violations >= self::ESCALATION_THRESHOLD) {
            return 'ESCALATE';
        }

        return $response;
    }

    public function violationCount(string $response, ?string $locale = null, array $allowedAmounts = []): int
    {
        $violations = 0;

        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (preg_match($pattern, $response)) {
                $violations++;
            }
        }

        // Check for unauthorized amounts: only amounts NOT in allowedPrices are violations
        if (preg_match_all(self::AMOUNT_PATTERN, $response, $matches)) {
            foreach ($matches[0] as $idx => $fullMatch) {
                $amount = (int) $matches[1][$idx];
                $currency = $matches[2][$idx];

                // Convert to centimes for comparison
                $centimes = $amount * 100;

                // If this amount is a known consultation price, it's allowed
                if (in_array($centimes, $allowedAmounts, true)) {
                    continue;
                }

                // Any other amount is a violation (likely an act fee)
                $violations++;
            }
        }

        return $violations;
    }

    public function hasViolations(string $response, ?string $locale = null, array $allowedAmounts = []): bool
    {
        return $this->violationCount($response, $locale, $allowedAmounts) > 0;
    }

    public function clean(string $response, array $allowedAmounts = []): string
    {
        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            $response = preg_replace($pattern, '[contenu filtré]', $response);
        }

        // Only clean amounts NOT in allowedPrices
        if (preg_match_all(self::AMOUNT_PATTERN, $response, $matches)) {
            foreach ($matches[0] as $idx => $fullMatch) {
                $amount = (int) $matches[1][$idx];
                $centimes = $amount * 100;

                if (! in_array($centimes, $allowedAmounts, true)) {
                    $response = str_replace($fullMatch, '[contenu filtré]', $response);
                }
            }
        }

        return $response;
    }

    public function getAllowedAmounts(): array
    {
        return ConsultationPlan::where('is_active', true)
            ->pluck('price_centimes')
            ->unique()
            ->values()
            ->toArray();
    }
}
