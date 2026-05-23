<?php

use App\Domain\Services\Chatbot\ChatbotService;
use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Domain\Services\Chatbot\EscalationHandler;
use App\Domain\Services\Chatbot\IntentClassifier;
use App\Domain\Services\Chatbot\OutputFilter;
use App\Domain\Services\Chatbot\TriageFlow;
use App\Models\ChatbotConversation;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('fr');
    $this->llm = Mockery::mock(LlmClient::class);
    $this->llm->shouldReceive('countTokens')->andReturn(10)->byDefault();

    $this->service = new ChatbotService(
        $this->llm,
        new IntentClassifier,
        new TriageFlow,
        new EscalationHandler,
        new OutputFilter,
    );
});

afterEach(function () {
    Mockery::close();
});

test('startConversation creates a new conversation', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    expect($conversation)->toBeInstanceOf(ChatbotConversation::class);
    expect($conversation->session_id)->toBe('test-session');
    expect($conversation->locale)->toBe('fr');
    expect($conversation->intent_resolved)->toBe('info_only');
});

test('startConversation reuses existing active conversation', function () {
    $first = $this->service->startConversation('test-session', 'fr');
    $second = $this->service->startConversation('test-session', 'fr');

    expect($second->id)->toBe($first->id);
});

test('respondTo handles empty message', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = iterator_to_array($this->service->respondTo($conversation, ''));

    expect($response[0])->toBeString();
    expect(strlen($response[0]))->toBeGreaterThan(0);
});

test('respondTo handles greeting', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = iterator_to_array($this->service->respondTo($conversation, 'Bonjour'));

    expect($response[0])->toBeString();
    expect(strlen($response[0]))->toBeGreaterThan(0);
});

test('respondTo handles escalation', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = iterator_to_array($this->service->respondTo($conversation, 'Je veux parler à un agent'));

    expect(strlen($response[0]))->toBeGreaterThan(0);
    expect($conversation->fresh()->intent_resolved)->toBe('escalated');
});

test('respondTo handles out of scope', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = iterator_to_array($this->service->respondTo($conversation, 'Mon procès au tribunal'));

    expect($response[0])->toBeString();
    expect(strlen($response[0]))->toBeGreaterThan(0);
    expect($conversation->fresh()->intent_resolved)->toBe('out_of_scope');
});

test('respondTo handles booking intent with triage', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = iterator_to_array($this->service->respondTo($conversation, 'Je veux prendre un rendez-vous'));

    expect(strlen($response[0]))->toBeGreaterThan(0);
    expect($conversation->fresh()->metadata['triage_state'])->toBe('active');
});

test('respondTo generates LLM response for FAQ query', function () {
    Faq::factory()->create([
        'question_translations' => ['fr' => 'Documents pour mariage'],
        'answer_translations' => ['fr' => 'CIN, acte de naissance'],
        'is_published' => true,
    ]);

    $this->llm->shouldReceive('generate')
        ->once()
        ->with(Mockery::any(), Mockery::any())
        ->andReturn('Pour un mariage, vous aurez besoin de CIN et acte de naissance. Pour plus d\'informations, prenez rendez-vous.');

    $conversation = $this->service->startConversation('test-session', 'fr');
    $response = iterator_to_array($this->service->respondTo($conversation, 'Quels documents pour un mariage ?'));

    expect($response[0])->toContain('mariage');
});

test('getMonthlyCost returns zero with no messages', function () {
    expect($this->service->getMonthlyCost())->toBe(0.0);
});

test('isBudgetExhausted returns false by default', function () {
    expect($this->service->isBudgetExhausted())->toBeFalse();
});
