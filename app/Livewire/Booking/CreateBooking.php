<?php

namespace App\Livewire\Booking;

use App\Domain\Services\AvailabilityService;
use App\Domain\Services\BookingService;
use App\Domain\Services\PaymentService;
use App\Enums\BookingFormat;
use App\Enums\Locale;
use App\Enums\ServiceCategory;
use App\Events\BookingCreated;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\ValueObjects\BookingData;
use App\ValueObjects\MoroccanPhoneNumber;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateBooking extends Component
{
    public BookingFormState $state;

    public int $step = 1;

    public ?int $selectedPlanId = null;

    public ?string $selectedDate = null;

    public array $plans = [];

    public array $availableSlots = [];

    public array $slotWarnings = [];

    public ?string $error = null;

    public ?string $bookingReference = null;

    public ?int $bookingId = null;

    public bool $loadingSlots = false;

    public bool $processing = false;

    public string $calendarMonth = '';

    protected $queryString = ['step' => ['except' => 1]];

    public function mount(): void
    {
        $this->calendarMonth = now()->format('Y-m');

        $this->plans = ConsultationPlan::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->toArray();

        $planSlug = request()->query('plan');
        $categorySlug = request()->query('category');
        $formatSlug = request()->query('format');

        if ($planSlug) {
            $plan = ConsultationPlan::where('slug', $planSlug)->where('is_active', true)->first();
            if ($plan) {
                $this->state->planId = $plan->id;
                $this->selectedPlanId = $plan->id;
                $this->step = 2;
            }
        }

        if ($categorySlug && $this->step >= 2) {
            $this->state->category = $categorySlug;
        }

        if ($formatSlug && $this->step >= 2) {
            $this->state->format = $formatSlug;
        }

        $this->state->preferredChannel = 'email';
    }

    public function selectPlan(int $planId): void
    {
        $this->state->planId = $planId;
        $this->selectedPlanId = $planId;
        $this->step = 2;
    }

    public function markHasDocuments(): void
    {
        $this->state->hasDocuments = true;
    }

    public function markNoDocuments(): void
    {
        $this->state->hasDocuments = false;
    }

    public function selectCategory(string $value): void
    {
        $this->state->category = $value;
    }

    public function selectFormat(string $value): void
    {
        $this->state->format = $value;
    }

    public function selectChannel(string $value): void
    {
        $this->state->preferredChannel = $value;
    }

    public function submitStep2(): void
    {
        $this->validate([
            'state.category' => 'required|string',
            'state.description' => 'required|string|min:20|max:2000',
            'state.hasDocuments' => 'required|boolean',
        ]);

        $plan = $this->getPlan();
        if ($plan && $plan['format'] === 'both' && ! $this->state->format) {
            $this->addError('state.format', __('booking.validation.format_required'));

            return;
        }

        $this->step = 3;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadingSlots = true;

        $this->loadSlots();
    }

    public function loadSlots(): void
    {
        $this->loadingSlots = true;

        try {
            $plan = $this->getPlan();
            if (! $plan) {
                $this->availableSlots = [];

                return;
            }

            $format = $this->resolveFormat();
            $availabilityService = app(AvailabilityService::class);

            $from = $this->selectedDate
                ? CarbonImmutable::parse($this->selectedDate)->startOfDay()
                : CarbonImmutable::today();
            $to = $this->selectedDate
                ? CarbonImmutable::parse($this->selectedDate)->endOfDay()
                : CarbonImmutable::today()->addDays(30);

            $planModel = ConsultationPlan::findOrFail($plan['id']);
            $slots = $availabilityService->availableSlots($from, $to, $planModel, $format);

            $this->availableSlots = collect($slots)
                ->filter(fn (TimeSlot $slot) => $slot->startsAt->greaterThan(now()->addHours(2)))
                ->map(fn (TimeSlot $slot) => [
                    'starts_at' => $slot->startsAt->toIso8601String(),
                    'ends_at' => $slot->endsAt->toIso8601String(),
                    'label' => $slot->startsAt->format('H:i').' - '.$slot->endsAt->format('H:i'),
                ])
                ->values()
                ->toArray();
        } finally {
            $this->loadingSlots = false;
        }
    }

    public function selectSlot(string $startsAt, string $endsAt): void
    {
        $this->state->slotStartsAt = $startsAt;

        try {
            $slot = new TimeSlot(
                CarbonImmutable::parse($startsAt),
                CarbonImmutable::parse($endsAt),
            );

            $availabilityService = app(AvailabilityService::class);
            $availabilityService->assertSlotIsFree($slot, $this->resolveFormat());

            $hold = $availabilityService->holdSlot(
                $slot,
                session()->getId(),
            );

            $this->state->holdId = $hold->id;
            $this->step = 4;
            $this->error = null;
        } catch (\RuntimeException $e) {
            $this->error = __('booking.errors.slot_taken');
            $this->loadSlots();
        }
    }

    public function submitStep4(): void
    {
        $this->validate([
            'state.fullName' => 'required|string|min:3|max:160',
            'state.email' => 'required|email|max:255',
            'state.phone' => 'required|string|regex:/^0[5-7]\d{8}$/',
            'state.preferredChannel' => 'nullable|in:email,sms,whatsapp',
            'state.acceptedTerms' => 'accepted',
            'state.acceptedPrivacy' => 'accepted',
        ]);

        $this->step = 5;
    }

    public function skipToPayment(): void
    {
        $this->step = 6;
    }

    public function submitStep5(): void
    {
        $this->step = 6;
    }

    public function getPlanPrice(): int
    {
        $plan = $this->getPlan();

        return $plan ? (int) $plan['price_centimes'] : 0;
    }

    public function getPlan(): ?array
    {
        if (! $this->state->planId) {
            return null;
        }

        return collect($this->plans)->firstWhere('id', $this->state->planId);
    }

    public function isFreePlan(): bool
    {
        return $this->getPlanPrice() === 0;
    }

    public function showCashOption(): bool
    {
        $plan = $this->getPlan();
        if (! $plan) {
            return false;
        }

        return $plan['format'] === 'in_office' || $plan['format'] === 'both';
    }

    public function setPaymentMethod(string $method): void
    {
        $this->state->paymentMethod = $method;
    }

    public function confirmBooking(): void
    {
        $this->processing = true;
        $this->error = null;

        try {
            $plan = $this->getPlan();
            if (! $plan) {
                throw new \RuntimeException('Plan not found');
            }

            $bookingService = app(BookingService::class);
            $paymentService = app(PaymentService::class);

            $client = Client::where('email', $this->state->email)->first();
            if (! $client) {
                $client = Client::create([
                    'uuid' => (string) Str::uuid(),
                    'email' => $this->state->email,
                    'phone' => $this->state->phone,
                    'full_name' => $this->state->fullName,
                    'preferred_locale' => app()->getLocale(),
                    'national_id' => $this->state->nationalId,
                ]);
            }

            $format = $this->resolveFormat();
            $slot = new TimeSlot(
                CarbonImmutable::parse($this->state->slotStartsAt),
                CarbonImmutable::parse($this->state->slotStartsAt)->addMinutes((int) $plan['duration_minutes']),
            );

            $bookingData = new BookingData(
                consultationPlanId: $plan['id'],
                serviceCategory: ServiceCategory::from($this->state->category),
                format: $format,
                slot: $slot,
                clientFullName: $this->state->fullName,
                clientEmail: $this->state->email,
                clientPhone: MoroccanPhoneNumber::fromInput($this->state->phone),
                description: $this->state->description,
                locale: Locale::tryFrom(app()->getLocale()) ?? Locale::FR,
                totalCentimes: $plan['price_centimes'],
            );

            if ($this->isFreePlan() && $client->hasExceededFreeOrientationLimit()) {
                throw new \RuntimeException(__('booking.errors.free_orientation_limit'));
            }

            $booking = $bookingService->createPending($bookingData, $client);

            BookingCreated::dispatch($booking);

            if ($this->isFreePlan()) {
                $bookingService->confirm($booking);
                $this->bookingReference = $booking->reference;
                $this->bookingId = $booking->id;
                $this->step = 7;

                return;
            }

            if ($this->state->paymentMethod === 'cash') {
                $paymentService->createCashPending($booking);
                $bookingService->confirm($booking);
                $this->bookingReference = $booking->reference;
                $this->bookingId = $booking->id;
                $this->step = 7;

                return;
            }

            $intent = $paymentService->createIntent($booking);
            $this->bookingReference = $booking->reference;
            $this->bookingId = $booking->id;

            $this->dispatch('stripe-payment', [
                'clientSecret' => $intent['client_secret'],
                'intentId' => $intent['intent_id'],
                'reference' => $booking->reference,
            ]);
        } catch (\Throwable $e) {
            $this->processing = false;
            $this->error = $e->getMessage();
        }
    }

    public function handlePaymentSuccess(string $reference): void
    {
        $this->bookingReference = $reference;
        $this->step = 7;
    }

    public function handlePaymentError(string $message): void
    {
        $this->processing = false;
        $this->error = $message;
    }

    public function goBack(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function daysWithSlots(): array
    {
        $availabilityService = app(AvailabilityService::class);
        $plan = $this->getPlan();

        if (! $plan) {
            return [];
        }

        $maxDays = $this->isFreePlan() ? 7 : 30;
        $format = $this->resolveFormat();
        $planModel = ConsultationPlan::findOrFail($plan['id']);
        $days = [];

        $from = CarbonImmutable::today();
        $to = $from->addDays($maxDays);

        $current = $from->copy();
        while ($current->lessThanOrEqualTo($to)) {
            $slots = $availabilityService->availableSlots(
                $current->startOfDay(),
                $current->endOfDay(),
                $planModel,
                $format,
            );

            $futureSlots = collect($slots)->filter(
                fn (TimeSlot $slot) => $slot->startsAt->greaterThan(now()->addHours(2)),
            );

            if ($futureSlots->isNotEmpty()) {
                $days[] = $current->format('Y-m-d');
            }

            $current = $current->addDay();
        }

        return $days;
    }

    public $files = [];

    public array $temporaryFiles = [];

    public function previousMonth(): void
    {
        $this->calendarMonth = CarbonImmutable::parse($this->calendarMonth.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->calendarMonth = CarbonImmutable::parse($this->calendarMonth.'-01')->addMonth()->format('Y-m');
    }

    public function calendarMonthLabel(): string
    {
        return CarbonImmutable::parse($this->calendarMonth.'-01')->locale(app()->getLocale())->isoFormat('MMMM YYYY');
    }

    public function calendarDays(): array
    {
        $date = CarbonImmutable::parse($this->calendarMonth.'-01');
        $startOfMonth = $date->startOfMonth();
        $endOfMonth = $date->endOfMonth();
        $days = [];

        $padding = (($startOfMonth->dayOfWeekIso - 1) + 7) % 7;
        for ($i = 0; $i < $padding; $i++) {
            $days[] = ['empty' => true, 'date' => null];
        }

        $current = $startOfMonth->copy();
        while ($current <= $endOfMonth) {
            $days[] = ['empty' => false, 'date' => $current];
            $current = $current->addDay();
        }

        return $days;
    }

    public function removeFile(string $uuid): void
    {
        $this->temporaryFiles = array_values(array_filter(
            $this->temporaryFiles,
            fn ($f) => $f['uuid'] !== $uuid,
        ));
    }

    public function updatedFiles(): void
    {
        $this->validate([
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        foreach ($this->files as $file) {
            $uuid = (string) Str::uuid();
            $path = $file->store('temp-uploads', 'local');

            $this->temporaryFiles[] = [
                'uuid' => $uuid,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'path' => $path,
                'mime' => $file->getMimeType(),
            ];
        }

        $this->files = [];
    }

    private function resolveFormat(): BookingFormat
    {
        $plan = $this->getPlan();
        if (! $plan) {
            return BookingFormat::ONLINE;
        }

        return match ($plan['format']) {
            'in_office' => BookingFormat::IN_OFFICE,
            'both' => $this->state->format === 'in_office'
                ? BookingFormat::IN_OFFICE
                : BookingFormat::ONLINE,
            default => BookingFormat::ONLINE,
        };
    }

    public function render()
    {
        return view('livewire.booking.create-booking')
            ->layout('layouts.public');
    }
}
