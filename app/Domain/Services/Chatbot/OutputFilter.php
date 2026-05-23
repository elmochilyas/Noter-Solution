<?php

namespace App\Domain\Services\Chatbot;

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

        // MAD/DH amounts not inside a context block
        '/\b\d{1,3}(?:\s?)(?:DH|MAD|د\.م\.|درهم)\b(?!.*(?:contexte|context))/i',
    ];

    private const ESCALATION_THRESHOLD = 2;

    public function filter(string $response): string
    {
        $violations = $this->violationCount($response);

        if ($violations >= self::ESCALATION_THRESHOLD) {
            return 'ESCALATE';
        }

        return $response;
    }

    public function violationCount(string $response): int
    {
        $violations = 0;

        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (preg_match($pattern, $response)) {
                $violations++;
            }
        }

        return $violations;
    }

    public function hasViolations(string $response): bool
    {
        return $this->violationCount($response) > 0;
    }

    public function clean(string $response): string
    {
        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            $response = preg_replace($pattern, '[contenu filtré]', $response);
        }

        return $response;
    }
}
