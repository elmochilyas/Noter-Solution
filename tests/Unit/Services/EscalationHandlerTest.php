<?php

use App\Domain\Services\Chatbot\EscalationHandler;

beforeEach(function () {
    $this->handler = new EscalationHandler;
});

test('shouldEscalate detects keywords in French', function () {
    expect($this->handler->shouldEscalate('Je veux parler à un agent', 'fr'))->toBeTrue();
    expect($this->handler->shouldEscalate('Urgence', 'fr'))->toBeTrue();
    expect($this->handler->shouldEscalate('Document normal', 'fr'))->toBeFalse();
});

test('shouldEscalate detects keywords in Arabic', function () {
    expect($this->handler->shouldEscalate('أريد التحدث مع وكيل', 'ar'))->toBeTrue();
    expect($this->handler->shouldEscalate('رقم الهاتف', 'ar'))->toBeTrue();
    expect($this->handler->shouldEscalate('وثائق عادية', 'ar'))->toBeFalse();
});

test('detectLoop returns true for repeated questions', function () {
    $messages = [
        ['role' => 'user', 'content' => 'Quels documents pour un mariage ?'],
        ['role' => 'assistant', 'content' => 'Voici les documents...'],
        ['role' => 'user', 'content' => 'Quels documents pour un mariage ?'],
        ['role' => 'assistant', 'content' => 'Voici les documents...'],
    ];

    expect($this->handler->detectLoop($messages))->toBeTrue();
});

test('detectLoop returns false for different questions', function () {
    $messages = [
        ['role' => 'user', 'content' => 'Quels documents pour un mariage ?'],
        ['role' => 'assistant', 'content' => 'Voici les documents...'],
        ['role' => 'user', 'content' => 'Combien ça coûte ?'],
        ['role' => 'assistant', 'content' => 'Veuillez prendre RDV...'],
    ];

    expect($this->handler->detectLoop($messages))->toBeFalse();
});

test('detectLoop returns false for short history', function () {
    expect($this->handler->detectLoop([]))->toBeFalse();
    expect($this->handler->detectLoop([['role' => 'user', 'content' => 'Test']]))->toBeFalse();
});
