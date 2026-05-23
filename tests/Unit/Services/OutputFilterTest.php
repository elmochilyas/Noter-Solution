<?php

use App\Domain\Services\Chatbot\OutputFilter;

beforeEach(function () {
    $this->filter = new OutputFilter;
});

test('allows normal responses through', function () {
    $response = 'Pour un mariage, vous aurez besoin des pièces suivantes : CIN, acte de naissance.';
    expect($this->filter->filter($response))->toBe($response);
});

test('flags two or more violations as ESCALATE', function () {
    $response = 'Je suis le meilleur avocat et je donne un conseil juridique gratuitement.';
    expect($this->filter->filter($response))->toBe('ESCALATE');
});

test('allows single violation through filter', function () {
    $response = 'Je suis le meilleur assistant pour vous aider.';
    expect($this->filter->filter($response))->toBe($response);
});

test('hasViolations returns true for any violation', function () {
    expect($this->filter->hasViolations('Je suis le meilleur'))->toBeTrue();
    expect($this->filter->hasViolations('Document normal'))->toBeFalse();
});

test('violationCount returns correct count', function () {
    expect($this->filter->violationCount('Normal response'))->toBe(0);
    expect($this->filter->violationCount('Le meilleur'))->toBe(1);
    expect($this->filter->violationCount('Le meilleur conseil juridique'))->toBe(2);
});

test('clean removes forbidden patterns', function () {
    $response = 'Je suis le meilleur avocat.';
    $cleaned = $this->filter->clean($response);
    expect($cleaned)->not->toContain('meilleur');
    expect($cleaned)->toContain('[contenu filtré]');
});

test('detects fee amounts', function () {
    $response = 'Cela coûte 500 DH pour cette consultation.';
    expect($this->filter->filter($response))->toBe($response);
    expect($this->filter->hasViolations($response))->toBeTrue();
});

test('clean removes fee amounts', function () {
    $response = 'Cela coûte 500 DH.';
    $cleaned = $this->filter->clean($response);
    expect($cleaned)->toContain('[contenu filtré]');
});
