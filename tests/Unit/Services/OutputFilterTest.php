<?php

use App\Domain\Services\Chatbot\OutputFilter;
use App\Models\ConsultationPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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

test('detects unauthorized fee amounts as violations', function () {
    $response = 'Cela coûte 500 DH pour cette consultation.';
    expect($this->filter->filter($response))->toBe($response);
    expect($this->filter->hasViolations($response))->toBeTrue();
});

test('clean removes unauthorized fee amounts', function () {
    $response = 'Cela coûte 500 DH.';
    $cleaned = $this->filter->clean($response);
    expect($cleaned)->toContain('[contenu filtré]');
});

test('allows known consultation prices when passed as allowed', function () {
    $response = 'La consultation standard est à 250 MAD.';
    $allowed = [25000]; // 250 MAD in centimes
    expect($this->filter->violationCount($response, 'fr', $allowed))->toBe(0);
    expect($this->filter->hasViolations($response, 'fr', $allowed))->toBeFalse();
});

test('allows multiple consultation prices', function () {
    $response = 'Nos tarifs : orientation gratuite 0 MAD, standard 250 MAD, cabinet 400 MAD, étendu 800 MAD.';
    $allowed = [0, 25000, 40000, 80000];
    expect($this->filter->violationCount($response, 'fr', $allowed))->toBe(0);
});

test('still catches unauthorized amounts even with allowed prices', function () {
    $response = 'L\'acte coûte 1500 MAD.';
    $allowed = [0, 25000, 40000, 80000];
    expect($this->filter->violationCount($response, 'fr', $allowed))->toBe(1);
});

test('mixed allowed and unauthorized amounts counts only unauthorized', function () {
    $response = 'Consultation à 250 MAD et acte à 1500 MAD.';
    $allowed = [0, 25000, 40000, 80000];
    // 250 MAD (25000 centimes) = allowed, 1500 MAD (150000 centimes) = violation
    expect($this->filter->violationCount($response, 'fr', $allowed))->toBe(1);
});

test('clean only removes unauthorized amounts, keeps allowed ones', function () {
    $response = 'Consultation à 250 MAD et acte à 5000 DH.';
    $allowed = [25000];
    $cleaned = $this->filter->clean($response, $allowed);
    expect($cleaned)->toContain('250 MAD');
    expect($cleaned)->not->toContain('5000 DH');
    expect($cleaned)->toContain('[contenu filtré]');
});

test('getAllowedAmounts returns empty array when no plans exist', function () {
    expect($this->filter->getAllowedAmounts())->toBe([]);
});

test('getAllowedAmounts returns active plan prices', function () {
    ConsultationPlan::factory()->create([
        'slug' => 'standard-online',
        'price_centimes' => 25000,
        'is_active' => true,
    ]);

    $prices = $this->filter->getAllowedAmounts();
    expect($prices)->toContain(25000);
    expect($prices)->toHaveCount(1);
});
