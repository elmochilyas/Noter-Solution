<?php

use App\Enums\ChatbotIntent;

test('ChatbotIntent has all expected cases', function () {
    expect(ChatbotIntent::GREETING->value)->toBe('greeting');
    expect(ChatbotIntent::FAQ_QUERY->value)->toBe('faq_query');
    expect(ChatbotIntent::BOOKING_INTENT->value)->toBe('booking_intent');
    expect(ChatbotIntent::PRICING_QUERY->value)->toBe('pricing_query');
    expect(ChatbotIntent::ESCALATION->value)->toBe('escalation');
    expect(ChatbotIntent::OUT_OF_SCOPE->value)->toBe('out_of_scope');
});
