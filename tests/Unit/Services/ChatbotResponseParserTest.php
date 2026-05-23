<?php

use App\Domain\Chatbot\ChatbotResponse;
use App\Services\Chatbot\ChatbotResponseParser;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->parser = new ChatbotResponseParser;
});

test('parses standard valid JSON', function () {
    $json = '{"answer": "Test answer.", "suggestions": ["Follow up?"], "escalate": false, "out_of_scope": false}';
    $response = $this->parser->parse($json);

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBe('Test answer.');
    expect($response->suggestions)->toBe(['Follow up?']);
    expect($response->escalate)->toBeFalse();
    expect($response->outOfScope)->toBeFalse();
});

test('strips markdown code fences', function () {
    $json = '```json
{"answer": "Answer with fences.", "suggestions": ["Next question?"]}
```';
    $response = $this->parser->parse($json);

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBe('Answer with fences.');
    expect($response->suggestions)->toBe(['Next question?']);
});

test('strips leading prose before JSON', function () {
    $json = 'Voici la réponse demandée :
{"answer": "Answer with prose prefix.", "suggestions": []}';
    $response = $this->parser->parse($json);

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBe('Answer with prose prefix.');
});

test('strips trailing prose after JSON', function () {
    $json = '{"answer": "Answer with trailing prose.", "suggestions": []} Et voilà !';
    $response = $this->parser->parse($json);

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toBe('Answer with trailing prose.');
});

test('handles completely invalid JSON with fallback', function () {
    $response = $this->parser->parse('Ceci n\'est pas du JSON', 'fr');

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toContain('Désolé');
    expect($response->suggestions)->toContain('Reformuler');
});

test('handles empty JSON object with fallback', function () {
    $response = $this->parser->parse('{}', 'fr');

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toContain('Désolé');
});

test('handles missing answer field with fallback', function () {
    $response = $this->parser->parse('{"suggestions": []}', 'fr');

    expect($response)->toBeInstanceOf(ChatbotResponse::class);
    expect($response->answer)->toContain('Désolé');
});

test('tolerates extra unknown fields', function () {
    $json = '{"answer": "Main answer.", "suggestions": ["Q1"], "extra_field": "ignored", "nested": {"ignored": true}}';
    $response = $this->parser->parse($json);

    expect($response->answer)->toBe('Main answer.');
    expect($response->suggestions)->toBe(['Q1']);
});

test('limits suggestions to 4', function () {
    $json = '{"answer": "Test.", "suggestions": ["A", "B", "C", "D", "E", "F"]}';
    $response = $this->parser->parse($json);

    expect($response->suggestions)->toHaveCount(4);
});

test('filters out reverse-direction French suggestions', function () {
    $json = '{"answer": "Test.", "suggestions": [
        "Combien coûte une consultation ?",
        "Quel est votre problème ?",
        "Avez-vous déjà pris rendez-vous ?",
        "Quels documents sont nécessaires ?"
    ]}';
    $response = $this->parser->parse($json);

    expect($response->suggestions)->toBe([
        'Combien coûte une consultation ?',
        'Quels documents sont nécessaires ?',
    ]);
    expect($response->suggestions)->not->toContain('Quel est votre problème ?');
    expect($response->suggestions)->not->toContain('Avez-vous déjà pris rendez-vous ?');
});

test('filters out reverse-direction Arabic suggestions', function () {
    $json = '{"answer": "Test.", "suggestions": [
        "كم ثمن الاستشارة ؟",
        "هل لديك وثائق الطلاق؟",
        "ما هو الموضوع الذي يهمك؟"
    ]}';
    $response = $this->parser->parse($json);

    expect($response->suggestions)->toBe([
        'كم ثمن الاستشارة ؟',
    ]);
    expect($response->suggestions)->not->toContain('هل لديك وثائق الطلاق؟');
    expect($response->suggestions)->not->toContain('ما هو الموضوع الذي يهمك؟');
});

test('parses recommended plan', function () {
    $json = '{"answer": "Test.", "suggestions": [], "recommended_plan": {"slug": "standard-online", "category": "family", "format": "online", "reason": "Best fit."}}';
    $response = $this->parser->parse($json);

    expect($response->recommendedPlan)->not->toBeNull();
    expect($response->recommendedPlan->slug)->toBe('standard-online');
    expect($response->recommendedPlan->category)->toBe('family');
});

test('parses null recommended plan', function () {
    $json = '{"answer": "Test.", "suggestions": [], "recommended_plan": null}';
    $response = $this->parser->parse($json);

    expect($response->recommendedPlan)->toBeNull();
});

test('handles multiple JSON objects — takes first', function () {
    $json = '{"answer": "First answer.", "suggestions": []} garbage {"answer": "Second.", "suggestions": []}';
    $response = $this->parser->parse($json);

    expect($response->answer)->toBe('First answer.');
});
