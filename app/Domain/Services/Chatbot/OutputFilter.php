<?php

namespace App\Domain\Services\Chatbot;

final class OutputFilter
{
    private const FORBIDDEN_PATTERNS = [
        '/\b(?:je suis un? avocat|i am a lawyer|أنا محام)\b/i',
        '/\b(?:conseil juridique|legal advice|استشارة قانونية)\b/i',
        '/\b(?:le meilleur|the best|الأفضل|la meilleure)\b/i',
        '/\b(?:le plus rapide|the fastest|الأسرع)\b/i',
        '/\b(?:garanti|guaranteed|مضمون)\b/i',
        '/\b\d{1,3}(?:\s?)(?:DH|MAD|د\.م\.|درهم)\b(?!.*contexte)/i',
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
