<?php

use App\Domain\Services\Chatbot\TriageFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('fr');
    $this->triage = new TriageFlow;
    $this->metadata = TriageFlow::initialMetadata();
});

test('start activates triage and sets first step', function () {
    $response = $this->triage->start($this->metadata);

    expect($this->metadata['triage_state'])->toBe('active');
    expect($this->metadata['triage_step'])->toBe('category');
    expect($response)->toBeString();
});

test('handles category step correctly', function () {
    $this->triage->start($this->metadata);

    $next = $this->triage->processStep('category', 'family', $this->metadata);

    expect($this->metadata['category'])->toBe('family');
    expect($this->metadata['triage_step'])->toBe('has_documents');
    expect($next)->toBeString();
});

test('rejects invalid category', function () {
    $this->triage->start($this->metadata);

    $next = $this->triage->processStep('category', 'invalid', $this->metadata);

    expect($this->metadata['category'])->toBeNull();
    expect(strlen($next))->toBeGreaterThan(0);
});

test('handles documents step', function () {
    $this->triage->start($this->metadata);
    $this->triage->processStep('category', 'family', $this->metadata);

    $next = $this->triage->processStep('has_documents', 'yes', $this->metadata);

    expect($this->metadata['has_documents'])->toBeTrue();
    expect($this->metadata['triage_step'])->toBe('format');
    expect($next)->toBeString();
});

test('handles format step', function () {
    $this->triage->start($this->metadata);
    $this->triage->processStep('category', 'family', $this->metadata);
    $this->triage->processStep('has_documents', 'yes', $this->metadata);

    $next = $this->triage->processStep('format', 'video', $this->metadata);

    expect($this->metadata['format'])->toBe('video');
    expect($this->metadata['triage_step'])->toBe('urgency');
    expect($next)->toBeString();
});

test('rejects invalid format', function () {
    $this->triage->start($this->metadata);
    $this->triage->processStep('category', 'family', $this->metadata);
    $this->triage->processStep('has_documents', 'yes', $this->metadata);

    $next = $this->triage->processStep('format', 'invalid', $this->metadata);

    expect($this->metadata['format'])->toBeNull();
    expect(strlen($next))->toBeGreaterThan(0);
});

test('handles urgency step and completes triage', function () {
    $this->triage->start($this->metadata);
    $this->triage->processStep('category', 'real_estate', $this->metadata);
    $this->triage->processStep('has_documents', 'no', $this->metadata);
    $this->triage->processStep('format', 'in_person', $this->metadata);

    $next = $this->triage->processStep('urgency', 'this_week', $this->metadata);

    expect($this->metadata['urgency'])->toBe('this_week');
    expect($this->metadata['triage_state'])->toBe('completed');
    expect($next)->toBeNull();
});

test('triage category question has no inline bullet list', function () {
    $fr = __('chatbot.triage_category_question', [], 'fr');
    $ar = __('chatbot.triage_category_question', [], 'ar');

    expect($fr)->not->toContain('•');
    expect($ar)->not->toContain('•');
});

test('buildBookingUrl creates correct URL', function () {
    $metadata = [
        'category' => 'family',
        'format' => 'video',
    ];

    $url = TriageFlow::buildBookingUrl($metadata, 'fr');

    expect($url)->toContain('/fr/book');
    expect($url)->toContain('category=family');
    expect($url)->toContain('format=online');

    $urlAr = TriageFlow::buildBookingUrl($metadata, 'ar');
    expect($urlAr)->toContain('/ar/book');
});
