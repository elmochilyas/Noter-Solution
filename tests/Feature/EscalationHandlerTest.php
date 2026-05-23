<?php

use App\Domain\Services\Chatbot\EscalationHandler;
use App\Models\ChatbotConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('fr');
    $this->handler = new EscalationHandler;
});

test('buildResponse returns escalation array', function () {
    $conversation = ChatbotConversation::factory()->create([
        'locale' => 'fr',
    ]);

    $response = $this->handler->buildResponse($conversation);

    expect($response['type'])->toBe('escalation');
    expect($response['phone'])->toBeString();
    expect($response['whatsapp_link'])->toStartWith('https://wa.me/');
    expect($response['booking_link'])->toBe('/fr/book');
});

test('buildResponse includes summary when provided', function () {
    $conversation = ChatbotConversation::factory()->create([
        'locale' => 'fr',
    ]);

    $response = $this->handler->buildResponse($conversation, 'Summary text');

    expect($response['summary_opt_in'])->toBeFalse();
    expect($response['whatsapp_link'])->toContain(urlencode('Summary text'));
});
