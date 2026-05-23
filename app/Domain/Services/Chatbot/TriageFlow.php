<?php

namespace App\Domain\Services\Chatbot;

final class TriageFlow
{
    public const STEPS = ['category', 'has_documents', 'format', 'urgency'];

    public static function initialMetadata(): array
    {
        return [
            'triage_step' => null,
            'triage_state' => 'idle',
            'category' => null,
            'has_documents' => null,
            'format' => null,
            'urgency' => null,
        ];
    }

    public function start(array &$metadata): string
    {
        $metadata['triage_step'] = 'category';
        $metadata['triage_state'] = 'active';

        return __('chatbot.triage_category_question');
    }

    public function processStep(string $step, string $answer, array &$metadata): ?string
    {
        $validCategories = ['family', 'real_estate', 'financial', 'contracts', 'other'];
        $validFormats = ['in_person', 'video', 'indifferent'];
        $validUrgency = ['this_week', 'this_month', 'flexible'];

        return match ($step) {
            'category' => $this->handleCategory($answer, $metadata, $validCategories),
            'has_documents' => $this->handleDocuments($answer, $metadata),
            'format' => $this->handleFormat($answer, $metadata, $validFormats),
            'urgency' => $this->handleUrgency($answer, $metadata, $validUrgency),
            default => null,
        };
    }

    private function handleCategory(string $answer, array &$metadata, array $valid): ?string
    {
        if (! in_array($answer, $valid, true)) {
            return __('chatbot.triage_invalid_category');
        }

        $metadata['category'] = $answer;
        $metadata['triage_step'] = 'has_documents';

        return __('chatbot.triage_documents_question');
    }

    private function handleDocuments(string $answer, array &$metadata): ?string
    {
        $metadata['has_documents'] = $answer === 'yes';
        $metadata['triage_step'] = 'format';

        return __('chatbot.triage_format_question');
    }

    private function handleFormat(string $answer, array &$metadata, array $valid): ?string
    {
        if (! in_array($answer, $valid, true)) {
            return __('chatbot.triage_invalid_format');
        }

        $metadata['format'] = $answer;
        $metadata['triage_step'] = 'urgency';

        return __('chatbot.triage_urgency_question');
    }

    private function handleUrgency(string $answer, array &$metadata, array $valid): ?string
    {
        if (! in_array($answer, $valid, true)) {
            return __('chatbot.triage_invalid_urgency');
        }

        $metadata['urgency'] = $answer;
        $metadata['triage_state'] = 'completed';
        $metadata['triage_step'] = null;

        return null;
    }

    public static function buildBookingUrl(array $metadata, string $locale): string
    {
        $params = http_build_query([
            'category' => $metadata['category'] ?? '',
            'format' => $metadata['format'] === 'video' ? 'online' : 'in_office',
        ]);

        return "/{$locale}/book?{$params}";
    }
}
