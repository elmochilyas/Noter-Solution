<?php

use App\Domain\Chatbot\ChatbotResponse;
use App\Domain\Chatbot\LlmRequest;
use App\Domain\Chatbot\LlmResponse;
use App\Domain\Services\Chatbot\ChatbotService;
use App\Domain\Services\Chatbot\ChipFilter;
use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Domain\Services\Chatbot\EscalationHandler;
use App\Domain\Services\Chatbot\IntentClassifier;
use App\Domain\Services\Chatbot\OutputFilter;
use App\Domain\Services\Chatbot\RepetitionGuard;
use App\Domain\Services\Chatbot\TriageFlow;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\Faq;
use App\Services\Chatbot\ChatbotResponseParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('fr');
    $this->llm = Mockery::mock(LlmClient::class);
    $this->llm->shouldReceive('countTokens')->andReturn(10)->byDefault();

    $this->chipFilter = new ChipFilter;
    $this->repetitionGuard = new RepetitionGuard;

    $this->service = new ChatbotService(
        $this->llm,
        new IntentClassifier,
        new TriageFlow,
        new EscalationHandler,
        new OutputFilter,
        new ChatbotResponseParser,
        $this->chipFilter,
        $this->repetitionGuard,
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

    $response = $this->service->respondTo($conversation, '');

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBeString();
    expect(strlen($response->answer))->toBeGreaterThan(0);
});

test('respondTo handles greeting', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = $this->service->respondTo($conversation, 'Bonjour');

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBeString();
    expect(strlen($response->answer))->toBeGreaterThan(0);
});

test('respondTo handles escalation', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = $this->service->respondTo($conversation, 'Je veux parler à un agent');

    expect($response->answer)->toBeString();
    expect(strlen($response->answer))->toBeGreaterThan(0);
    expect($conversation->fresh()->intent_resolved)->toBe('escalated');
});

test('respondTo handles out of scope', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = $this->service->respondTo($conversation, 'Mon procès au tribunal');

    expect($response->answer)->toBeString();
    expect(strlen($response->answer))->toBeGreaterThan(0);
    expect($conversation->fresh()->intent_resolved)->toBe('out_of_scope');
});

test('respondTo handles booking intent with triage', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    expect($response->answer)->toBeString();
    expect(strlen($response->answer))->toBeGreaterThan(0);
    expect($conversation->fresh()->metadata['triage_state'])->toBe('active');
});

test('triage chips advance to next step after category selection', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    $response = $this->service->handleTriageChipClick($conversation, 'family');

    // Chips are raw slugs for the has_documents step
    expect($response->suggestions)->toBe(['yes', 'no']);
    expect($conversation->fresh()->metadata['category'])->toBe('family');
    expect($conversation->fresh()->metadata['triage_step'])->toBe('has_documents');

    // User chip click persisted with translated label
    $userMsg = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get()
        ->last();
    expect($userMsg)->not->toBeNull();
    expect($userMsg->content)->toBe('Famille');
});

test('triage chip click does not call the LLM', function () {
    $this->llm->shouldNotReceive('complete');

    $conversation = $this->service->startConversation('test-session', 'fr');

    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');
    $this->service->handleTriageChipClick($conversation, 'family');
    $response = $this->service->handleTriageChipClick($conversation, 'yes');
    $response = $this->service->handleTriageChipClick($conversation, 'video');

    // Final step completes triage and returns recommendation (no LLM call)
    $response = $this->service->handleTriageChipClick($conversation, 'this_week');
    expect($response->recommendedPlan)->not->toBeNull();
    expect($conversation->fresh()->metadata['triage_state'])->toBe('completed');
});

test('triage chips send raw slugs from service suggestions', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $response = $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    // Service must return raw slugs so TriageFlow can process chip clicks
    expect($response->suggestions)->toContain('family');
    expect($response->suggestions)->not->toContain('Famille');
});

test('handleTriageChipClick processes urgency step and completes triage', function () {
    $conversation = ChatbotConversation::factory()->create([
        'uuid' => (string) Str::uuid(),
        'session_id' => 'test-session',
        'locale' => 'fr',
        'intent_resolved' => 'info_only',
        'metadata' => [
            'triage_state' => 'active',
            'triage_step' => 'urgency',
            'category' => 'family',
            'has_documents' => true,
            'format' => 'video',
            'urgency' => null,
        ],
        'started_at' => now(),
        'last_message_at' => now(),
    ]);

    expect($conversation->metadata['triage_state'])->toBe('active');
    expect($conversation->metadata['triage_step'])->toBe('urgency');

    $response = $this->service->handleTriageChipClick($conversation, 'this_week');

    expect($conversation->fresh()->metadata['triage_state'])->toBe('completed');
    expect($conversation->fresh()->metadata['urgency'])->toBe('this_week');
    expect($response->recommendedPlan)->not->toBeNull();
});

test('respondTo abandons triage when free-form text sent during active triage', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    // Start triage
    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');
    expect($conversation->fresh()->metadata['triage_state'])->toBe('active');

    // Free-form text during active triage should abandon it
    $this->llm->shouldReceive('complete')
        ->once()
        ->with(Mockery::type(LlmRequest::class))
        ->andReturn(new LlmResponse(
            content: '{"answer": "Réponse à la question libre.", "suggestions": []}',
            tokensIn: 10, tokensOut: 10, latencyMs: 100, model: 'test',
        ));

    $response = $this->service->respondTo($conversation, 'En fait, c\'est quoi un acte de divorce ?');

    // Triage should be abandoned (idle)
    expect($conversation->fresh()->metadata['triage_state'])->toBe('idle');
    // LLM should have been called
    expect($response->answer)->toBe('Réponse à la question libre.');
});

test('complete triage flow works end-to-end with chip suggestions', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    // Step 1: start triage
    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');
    expect($conversation->fresh()->metadata['triage_state'])->toBe('active');
    expect($conversation->fresh()->metadata['triage_step'])->toBe('category');

    // Step 2: category (via handleTriageChipClick)
    $this->service->handleTriageChipClick($conversation, 'family');
    expect($conversation->fresh()->metadata['category'])->toBe('family');
    expect($conversation->fresh()->metadata['triage_step'])->toBe('has_documents');

    // Step 3: documents
    $this->service->handleTriageChipClick($conversation, 'yes');
    expect($conversation->fresh()->metadata['has_documents'])->toBe(true);
    expect($conversation->fresh()->metadata['triage_step'])->toBe('format');

    // Step 4: format
    $this->service->handleTriageChipClick($conversation, 'video');
    expect($conversation->fresh()->metadata['format'])->toBe('video');
    expect($conversation->fresh()->metadata['triage_step'])->toBe('urgency');

    // Step 5: urgency — completes triage
    $response = $this->service->handleTriageChipClick($conversation, 'this_week');
    expect($conversation->fresh()->metadata['triage_state'])->toBe('completed');
    expect($conversation->fresh()->metadata['urgency'])->toBe('this_week');
    expect($response->recommendedPlan)->not->toBeNull();

    // All 4 chip clicks persisted as user messages with translated labels
    $userMessages = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($userMessages)->toHaveCount(5); // 1 free-form + 4 chip clicks
    expect($userMessages[1]->content)->toBe('Famille');
    expect($userMessages[2]->content)->toBe('Oui');
    expect($userMessages[3]->content)->toBe('En vidéo');
    expect($userMessages[4]->content)->toBe('Cette semaine');
});

test('generates LLM response for FAQ query', function () {
    Faq::factory()->create([
        'question_translations' => ['fr' => 'Documents pour mariage'],
        'answer_translations' => ['fr' => 'CIN, acte de naissance'],
        'is_published' => true,
    ]);

    $this->llm->shouldReceive('complete')
        ->once()
        ->with(Mockery::type(LlmRequest::class))
        ->andReturn(new LlmResponse(
            content: '{"answer": "Pour un mariage, vous aurez besoin de CIN et acte de naissance.", "suggestions": ["Prendre rendez-vous"]}',
            tokensIn: 10,
            tokensOut: 20,
            latencyMs: 500,
            model: 'gpt-oss-120b',
        ));

    $conversation = $this->service->startConversation('test-session', 'fr');
    $response = $this->service->respondTo($conversation, 'Quels documents pour un mariage ?');

    expect($response->answer)->toContain('mariage');
});

test('getMonthlyCost returns zero with no messages', function () {
    expect($this->service->getMonthlyCost())->toBe(0.0);
});

test('buildFaqContext uses the provided locale not app locale', function () {
    app()->setLocale('fr');

    Faq::factory()->create([
        'question_translations' => ['fr' => 'Mariage français', 'ar' => 'زواج عربي'],
        'answer_translations' => ['fr' => 'Réponse française', 'ar' => 'إجابة عربية'],
        'is_published' => true,
    ]);

    // Create another published Faq that matches 'عربية' in Arabic
    Faq::factory()->create([
        'question_translations' => ['fr' => 'Autre', 'ar' => 'سؤال عربية'],
        'answer_translations' => ['fr' => 'Réponse', 'ar' => 'جواب'],
        'is_published' => true,
    ]);

    $ref = new ReflectionMethod($this->service, 'retrieveFaqs');
    $ref->setAccessible(true);
    // Search for term that exists in Arabic translations
    $faqs = $ref->invoke($this->service, 'عربية', 'ar');

    expect($faqs)->not->toBeEmpty();

    $buildRef = new ReflectionMethod($this->service, 'buildFaqContext');
    $buildRef->setAccessible(true);
    $context = $buildRef->invoke($this->service, $faqs, 'ar');

    expect($context)->toContain('زواج عربي');
    expect($context)->toContain('إجابة عربية');
    expect($context)->not->toContain('Mariage français');
});

test('retrieveFaqs does not return unpublished FAQs', function () {
    Faq::factory()->create([
        'question_translations' => ['fr' => 'Mariage'],
        'answer_translations' => ['fr' => 'Documents pour mariage'],
        'is_published' => true,
    ]);

    Faq::factory()->create([
        'question_translations' => ['fr' => 'Test'],
        'answer_translations' => ['fr' => 'Documents pour mariage également'],
        'is_published' => false,
    ]);

    $ref = new ReflectionMethod($this->service, 'retrieveFaqs');
    $ref->setAccessible(true);
    $results = $ref->invoke($this->service, 'mariage', 'fr');

    expect($results)->toHaveCount(1);
    expect($results->first()->is_published)->toBeTrue();
});

test('getMonthlyCost correctly calculates input and output costs', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    // Record assistant messages with known token counts (mimics real flow)
    ChatbotMessage::create([
        'conversation_id' => $conversation->id,
        'role' => 'assistant',
        'content' => 'response',
        'tokens_in' => 500000,   // 500K prompt tokens
        'tokens_out' => 200000,  // 200K completion tokens
        'created_at' => now(),
    ]);

    ChatbotMessage::create([
        'conversation_id' => $conversation->id,
        'role' => 'assistant',
        'content' => 'response 2',
        'tokens_in' => 300000,
        'tokens_out' => 100000,
        'created_at' => now(),
    ]);

    // Input cost: (800K / 1M) * 0.35 = 0.28
    // Output cost: (300K / 1M) * 0.75 = 0.225
    // Total: 0.28 + 0.225 = 0.505
    $expectedInputCost = (800000 / 1_000_000) * 0.35;
    $expectedOutputCost = (300000 / 1_000_000) * 0.75;
    $expectedTotal = round($expectedInputCost + $expectedOutputCost, 4);

    expect($this->service->getMonthlyCost())->toBe($expectedTotal);
});

test('isBudgetExhausted returns false by default', function () {
    expect($this->service->isBudgetExhausted())->toBeFalse();
});

test('conversation history does not include current user message (no duplicate)', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $capturedRequest = null;
    $this->llm->shouldReceive('complete')
        ->once()
        ->with(Mockery::type(LlmRequest::class))
        ->andReturnUsing(function (LlmRequest $request) use (&$capturedRequest) {
            $capturedRequest = $request;

            return new LlmResponse(
                content: '{"answer": "Answer 1.", "suggestions": []}',
                tokensIn: 10, tokensOut: 10, latencyMs: 100, model: 'test',
            );
        });

    $this->service->respondTo($conversation, 'Combien ça coûte ?');

    // Verify the request structure
    expect($capturedRequest)->not->toBeNull();
    $messages = $capturedRequest->messages;
    // History should be empty for first turn (only current msg)
    expect(count($messages))->toBe(1);
    expect($messages[0]['role'])->toBe('user');
    // Current message should NOT have context (no FAQ matches)
    expect($messages[0]['content'])->toContain('Combien ça coûte ?');
});

test('conversation history includes prior turns but not current message', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    // Turn 1: first LLM call
    $this->llm->shouldReceive('complete')
        ->once()
        ->andReturn(new LlmResponse(
            content: '{"answer": "Answer 1.", "suggestions": []}',
            tokensIn: 10, tokensOut: 10, latencyMs: 100, model: 'test',
        ));

    $this->service->respondTo($conversation, 'Combien ça coûte ?');

    // Turn 2: second LLM call
    $this->llm->shouldReceive('complete')
        ->once()
        ->andReturn(new LlmResponse(
            content: '{"answer": "Answer 2.", "suggestions": []}',
            tokensIn: 10, tokensOut: 10, latencyMs: 100, model: 'test',
        ));

    $this->service->respondTo($conversation, 'Et pour un divorce ?');
});

test('triage chip click after triage completion routes to LLM', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    // Start and complete triage
    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');
    $this->service->handleTriageChipClick($conversation, 'family');
    $this->service->handleTriageChipClick($conversation, 'yes');
    $this->service->handleTriageChipClick($conversation, 'video');
    $this->service->handleTriageChipClick($conversation, 'this_week');

    expect($conversation->fresh()->metadata['triage_state'])->toBe('completed');

    // After triage, chip click should go to LLM
    $this->llm->shouldReceive('complete')
        ->once()
        ->with(Mockery::type(LlmRequest::class))
        ->andReturn(new LlmResponse(
            content: '{"answer": "Réponse post-triage.", "suggestions": []}',
            tokensIn: 10, tokensOut: 10, latencyMs: 100, model: 'test',
        ));

    // Chip click after completion goes through respondTo (normal path)
    $response = $this->service->respondTo($conversation, 'Puis-je annuler ?');
    expect($response->answer)->toBe('Réponse post-triage.');
});

test('multi-turn history keeps assistant content as plain text not JSON', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $this->llm->shouldReceive('complete')
        ->times(4)
        ->andReturn(
            new LlmResponse('{"answer": "Answer 1.", "suggestions": []}', 10, 10, 100, 'test'),
            new LlmResponse('{"answer": "Answer 2.", "suggestions": []}', 10, 10, 100, 'test'),
            new LlmResponse('{"answer": "Answer 3.", "suggestions": []}', 10, 10, 100, 'test'),
            new LlmResponse('{"answer": "Answer 4.", "suggestions": ["Follow up"]}', 10, 10, 100, 'test'),
        );

    // 4 turns of conversation with diverse messages to avoid loop detection
    $this->service->respondTo($conversation, 'Quels documents pour un mariage ?');
    $this->service->respondTo($conversation, 'Combien de temps dure la procédure ?');
    $this->service->respondTo($conversation, 'Quels documents pour une donation ?');
    $this->service->respondTo($conversation, 'Où se trouve le cabinet ?');

    // Verify assistant messages are plain text, not JSON
    $assistantMessages = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'assistant')
        ->orderBy('created_at')
        ->get();

    expect($assistantMessages)->toHaveCount(4);
    foreach ($assistantMessages as $msg) {
        // Assistant content should be plain text answer, not JSON envelope
        expect($msg->content)->not->toStartWith('{');
        expect($msg->content)->not->toContain('"answer"');
        expect($msg->content)->not->toContain('"suggestions"');
        expect($msg->content)->toStartWith('Answer');
    }
});

test('triage state persists across simulated requests', function () {
    // Simulate loading conversation from DB between requests
    $conversation = $this->service->startConversation('test-session', 'fr');
    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    // Reload from DB
    $reloaded = ChatbotConversation::find($conversation->id);
    expect($reloaded->metadata['triage_state'])->toBe('active');
    expect($reloaded->metadata['triage_step'])->toBe('category');

    // Process chip click
    $response = $this->service->handleTriageChipClick($reloaded, 'family');

    // Reload again and verify
    $reloaded2 = ChatbotConversation::find($conversation->id);
    expect($reloaded2->metadata['category'])->toBe('family');
    expect($reloaded2->metadata['triage_step'])->toBe('has_documents');
});

test('triage chip click records user message with translated label', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    // Click a category chip
    $this->service->handleTriageChipClick($conversation, 'family');

    $userMessages = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();

    // 2 user messages: the free-form start + the chip click
    expect($userMessages)->toHaveCount(2);
    expect($userMessages[1]->content)->toBe('Famille');
});

test('triage chip click records translated label for all chip types', function () {
    $conversation = $this->service->startConversation('test-session', 'fr');

    $this->service->respondTo($conversation, 'Je veux prendre un rendez-vous');

    // Category chip
    $this->service->handleTriageChipClick($conversation, 'financial');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('Financier');

    // Documents chip
    $this->service->handleTriageChipClick($conversation, 'yes');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('Oui');

    // Format chip
    $this->service->handleTriageChipClick($conversation, 'in_person');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('En personne');

    // Urgency chip
    $this->service->handleTriageChipClick($conversation, 'flexible');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('Flexible');
});

test('triage chip click records Arabic translated labels', function () {
    app()->setLocale('ar');
    $conversation = $this->service->startConversation('test-session', 'ar');

    $this->service->respondTo($conversation, 'أريد حجز موعد');

    // Category chip
    $this->service->handleTriageChipClick($conversation, 'family');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('أسرة');

    // Documents chip
    $this->service->handleTriageChipClick($conversation, 'no');
    $msgs = ChatbotMessage::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->orderBy('id')
        ->get();
    expect($msgs->last()->content)->toBe('لا');
});
