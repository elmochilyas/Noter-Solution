<?php

namespace App\Domain\Services;

use App\Domain\Transitions\BookingStatusTransition;
use App\Enums\BookingFormat;
use App\Enums\BookingStatus;
use App\Enums\Locale;
use App\Enums\ServiceCategory;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\ValueObjects\BookingData;
use App\ValueObjects\BookingReference;
use App\ValueObjects\MoroccanPhoneNumber;
use App\ValueObjects\TimeSlot;

final class BookingService
{
    public function createPending(BookingData $data): Booking
    {
        $booking = new Booking;
        $booking->reference = (string) BookingReference::generate();
        $booking->consultation_plan_id = $data->consultationPlanId;
        $booking->service_category = $data->serviceCategory->value;
        $booking->format = $data->format->value;
        $booking->starts_at = $data->slot->startsAt;
        $booking->ends_at = $data->slot->endsAt;
        $booking->status = BookingStatus::PENDING_PAYMENT->value;
        $booking->description = $data->description;
        $booking->save();

        return $booking;
    }

    public function confirm(Booking $booking, ?Payment $payment = null): void
    {
        BookingStatusTransition::assertCanTransition(
            BookingStatus::from($booking->status),
            BookingStatus::CONFIRMED,
        );

        $booking->status = BookingStatus::CONFIRMED->value;
        $booking->save();
    }

    public function complete(Booking $booking): void
    {
        BookingStatusTransition::assertCanTransition(
            BookingStatus::from($booking->status),
            BookingStatus::COMPLETED,
        );

        $booking->status = BookingStatus::COMPLETED->value;
        $booking->completed_at = now();
        $booking->save();
    }

    public function markNoShow(Booking $booking): void
    {
        BookingStatusTransition::assertCanTransition(
            BookingStatus::from($booking->status),
            BookingStatus::NO_SHOW,
        );

        $booking->status = BookingStatus::NO_SHOW->value;
        $booking->save();
    }

    public function cancel(Booking $booking, string $reason, User|Client $by): void
    {
        BookingStatusTransition::assertCanTransition(
            BookingStatus::from($booking->status),
            BookingStatus::CANCELLED,
        );

        $booking->status = BookingStatus::CANCELLED->value;
        $booking->cancellation_reason = $reason;
        $booking->cancelled_at = now();
        $booking->save();
    }

    public function reschedule(Booking $booking, TimeSlot $newSlot): Booking
    {
        $this->cancel($booking, 'rescheduled', $booking->client);

        $data = new BookingData(
            consultationPlanId: $booking->consultation_plan_id,
            serviceCategory: ServiceCategory::from($booking->service_category),
            format: BookingFormat::from($booking->format),
            slot: $newSlot,
            clientFullName: $booking->client->full_name,
            clientEmail: $booking->client->email,
            clientPhone: MoroccanPhoneNumber::fromInput($booking->client->phone),
            description: $booking->description,
            locale: Locale::tryFrom(app()->getLocale()) ?? Locale::FR,
        );

        return $this->createPending($data);
    }
}
