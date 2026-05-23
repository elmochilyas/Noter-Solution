<?php

namespace App\Domain\Chatbot;

use InvalidArgumentException;

final readonly class PlanRecommendation
{
    private const VALID_SLUGS = [
        'free-orientation',
        'standard-online',
        'in-office',
        'extended',
    ];

    public function __construct(
        public string $slug,
        public string $category,
        public string $format,
        public string $reason,
    ) {
        if (! in_array($slug, self::VALID_SLUGS, true)) {
            throw new InvalidArgumentException("Invalid plan slug: {$slug}");
        }

        if (! in_array($category, ['family', 'real_estate', 'financial', 'contracts'], true)) {
            throw new InvalidArgumentException("Invalid category: {$category}");
        }

        if (! in_array($format, ['online', 'in_office'], true)) {
            throw new InvalidArgumentException("Invalid format: {$format}");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['slug'] ?? '',
            category: $data['category'] ?? '',
            format: $data['format'] ?? '',
            reason: $data['reason'] ?? '',
        );
    }

    public function toBookingUrl(string $locale): string
    {
        $params = http_build_query([
            'plan' => $this->slug,
            'category' => $this->category,
            'format' => $this->format,
        ]);

        return "/{$locale}/book?{$params}";
    }
}
