<?php

namespace App\Domain\Chatbot;

final readonly class LlmRequest
{
    public function __construct(
        public string $system,
        public array $messages,
        public int $maxTokens = 600,
        public float $temperature = 0.3,
        public ?string $responseFormat = 'json_object',
    ) {}
}
