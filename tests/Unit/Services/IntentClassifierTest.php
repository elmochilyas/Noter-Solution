<?php

use App\Domain\Services\Chatbot\IntentClassifier;
use App\Enums\ChatbotIntent;

beforeEach(function () {
    $this->classifier = new IntentClassifier;
});

test('classifies greetings in French', function () {
    expect($this->classifier->classify('Bonjour', 'fr'))->toBe(ChatbotIntent::GREETING);
    expect($this->classifier->classify('Salut, comment ça va ?', 'fr'))->toBe(ChatbotIntent::GREETING);
    expect($this->classifier->classify('Bonsoir', 'fr'))->toBe(ChatbotIntent::GREETING);
});

test('classifies greetings in Arabic', function () {
    expect($this->classifier->classify('السلام عليكم', 'ar'))->toBe(ChatbotIntent::GREETING);
    expect($this->classifier->classify('مرحبا', 'ar'))->toBe(ChatbotIntent::GREETING);
});

test('classifies booking intent in French', function () {
    expect($this->classifier->classify('Je veux prendre un rendez-vous', 'fr'))->toBe(ChatbotIntent::BOOKING_INTENT);
    expect($this->classifier->classify('Réserver une consultation', 'fr'))->toBe(ChatbotIntent::BOOKING_INTENT);
});

test('classifies booking intent in Arabic', function () {
    expect($this->classifier->classify('أريد حجز موعد', 'ar'))->toBe(ChatbotIntent::BOOKING_INTENT);
});

test('classifies pricing queries in French', function () {
    expect($this->classifier->classify('Combien ça coûte ?', 'fr'))->toBe(ChatbotIntent::PRICING_QUERY);
    expect($this->classifier->classify('Quel est le tarif ?', 'fr'))->toBe(ChatbotIntent::PRICING_QUERY);
});

test('classifies pricing queries in Arabic', function () {
    expect($this->classifier->classify('كم السعر؟', 'ar'))->toBe(ChatbotIntent::PRICING_QUERY);
});

test('classifies escalation in French', function () {
    expect($this->classifier->classify('Je veux parler à un agent', 'fr'))->toBe(ChatbotIntent::ESCALATION);
    expect($this->classifier->classify('WhatsApp', 'fr'))->toBe(ChatbotIntent::ESCALATION);
});

test('classifies escalation in Arabic', function () {
    expect($this->classifier->classify('أريد التحدث مع وكيل', 'ar'))->toBe(ChatbotIntent::ESCALATION);
});

test('classifies FAQ queries by default', function () {
    expect($this->classifier->classify('Quels documents pour un divorce ?', 'fr'))->toBe(ChatbotIntent::FAQ_QUERY);
    expect($this->classifier->classify('ما هي وثائق الزواج؟', 'ar'))->toBe(ChatbotIntent::FAQ_QUERY);
});

test('classifies out of scope in French', function () {
    expect($this->classifier->classify('Je veux aller au tribunal', 'fr'))->toBe(ChatbotIntent::OUT_OF_SCOPE);
    expect($this->classifier->classify('Mon procès est dans combien de temps ?', 'fr'))->toBe(ChatbotIntent::OUT_OF_SCOPE);
});

test('classifies out of scope in Arabic', function () {
    expect($this->classifier->classify('قضية أمام المحكمة', 'ar'))->toBe(ChatbotIntent::OUT_OF_SCOPE);
});
