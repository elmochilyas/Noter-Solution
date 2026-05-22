<?php

namespace App\Livewire\Booking;

use Livewire\Form;

class BookingFormState extends Form
{
    public ?int $planId = null;

    public ?string $category = null;

    public ?string $description = null;

    public ?bool $hasDocuments = null;

    public ?string $format = null;

    public ?string $slotStartsAt = null;

    public ?int $holdId = null;

    public ?string $fullName = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $preferredChannel = 'email';

    public ?string $nationalId = null;

    public bool $acceptedTerms = false;

    public bool $acceptedPrivacy = false;

    public array $uploadedFiles = [];

    public string $paymentMethod = 'card';
}
