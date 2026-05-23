<?php

namespace App\Domain\Services;

use App\Channels\TwilioSmsChannel;
use App\Channels\TwilioWhatsAppChannel;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\NotificationsLog;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\ShortLink;
use App\Notifications\AdminContactMessage;
use App\Notifications\AdminDispute;
use App\Notifications\AdminNewBooking;
use App\Notifications\AdminRefundRequest;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmation;
use App\Notifications\BookingReminder;
use App\Notifications\BookingRescheduledNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentReceipt;
use App\Notifications\RefundIssuedNotification;
use App\Notifications\TwilioSmsNotification;
use App\Notifications\TwilioWhatsAppNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Sentry\State\Scope;

final class NotificationService
{
    private const array QUIET_HOURS = ['start' => 22, 'end' => 7];

    public function sendBookingConfirmation(Booking $booking, array $channels = ['mail']): void
    {
        $this->send(
            templateKey: 'booking.confirmation',
            recipient: $booking->client,
            data: ['booking' => $booking],
            channels: $channels,
            notification: new BookingConfirmation($booking),
        );
    }

    public function sendBookingReminder(Booking $booking, string $type): void
    {
        $this->send(
            templateKey: 'booking.reminder.'.$type,
            recipient: $booking->client,
            data: ['booking' => $booking, 'type' => $type],
            channels: ['mail', 'sms', 'whatsapp'],
            notification: new BookingReminder($booking, $type),
        );
    }

    public function sendBookingCancelled(Booking $booking): void
    {
        $this->send(
            templateKey: 'booking.cancelled',
            recipient: $booking->client,
            data: ['booking' => $booking],
            channels: ['mail'],
            notification: new BookingCancelledNotification($booking),
        );
    }

    public function sendBookingRescheduled(Booking $oldBooking, Booking $newBooking): void
    {
        $this->send(
            templateKey: 'booking.rescheduled',
            recipient: $newBooking->client,
            data: ['old_booking' => $oldBooking, 'new_booking' => $newBooking],
            channels: ['mail'],
            notification: new BookingRescheduledNotification($oldBooking, $newBooking),
        );
    }

    public function sendPaymentReceipt(Receipt $receipt): void
    {
        $this->send(
            templateKey: 'payment.receipt',
            recipient: $receipt->booking?->client,
            data: ['receipt' => $receipt],
            channels: ['mail'],
            notification: new PaymentReceipt($receipt),
        );
    }

    public function sendPaymentFailed(Payment $payment): void
    {
        $this->send(
            templateKey: 'payment.failed',
            recipient: $payment->booking?->client,
            data: ['payment' => $payment],
            channels: ['mail'],
            notification: new PaymentFailedNotification($payment),
        );
    }

    public function sendRefundIssued(Refund $refund): void
    {
        $this->send(
            templateKey: 'refund.issued',
            recipient: $refund->payment?->booking?->client,
            data: ['refund' => $refund],
            channels: ['mail'],
            notification: new RefundIssuedNotification($refund),
        );
    }

    public function sendAdminNewBooking(Booking $booking): void
    {
        Notification::route('mail', config('mail.admin_address'))
            ->notify(new AdminNewBooking($booking));
    }

    public function sendAdminContactMessage(ContactMessage $message): void
    {
        Notification::route('mail', config('mail.admin_address'))
            ->notify(new AdminContactMessage($message));
    }

    public function sendAdminDispute(Booking $booking, string $message): void
    {
        Notification::route('mail', config('mail.admin_address'))
            ->notify(new AdminDispute($booking, $message));
    }

    public function sendAdminRefundRequest(Refund $refund): void
    {
        Notification::route('mail', config('mail.admin_address'))
            ->notify(new AdminRefundRequest($refund));
    }

    public function send(
        string $templateKey,
        ?Client $recipient,
        array $data,
        array $channels,
        \Illuminate\Notifications\Notification $notification,
    ): void {
        if (! $recipient) {
            return;
        }

        if ($this->isQuietHours() && ! in_array('mail', $channels)) {
            Log::info('Suppressed non-email notification during quiet hours', [
                'template' => $templateKey,
                'recipient' => $recipient->id,
            ]);

            $channels = array_merge($channels, ['mail']);
        }

        $resolvedChannels = $this->resolveChannels($recipient, $channels, $templateKey);

        $logs = [];
        foreach ($resolvedChannels as $channel => $address) {
            $logs[] = $this->createLogEntry($templateKey, $recipient, $channel, $address);
        }

        try {
            if (isset($resolvedChannels['mail'])) {
                $this->dispatchWithRateLimit($recipient, 'mail', $templateKey, function () use ($recipient, $notification): void {
                    $recipient->notify($notification);
                });
            }

            if (isset($resolvedChannels['sms'])) {
                $smsText = $this->getChannelText($templateKey, 'sms', $recipient, $data);
                $this->dispatchWithRateLimit($recipient, 'sms', $templateKey, function () use ($resolvedChannels, $smsText): void {
                    Notification::route(TwilioSmsChannel::class, $resolvedChannels['sms'])
                        ->notify(new TwilioSmsNotification($smsText));
                });
            }

            if (isset($resolvedChannels['whatsapp'])) {
                $waText = $this->getChannelText($templateKey, 'whatsapp', $recipient, $data);
                $this->dispatchWithRateLimit($recipient, 'whatsapp', $templateKey, function () use ($resolvedChannels, $waText): void {
                    Notification::route(TwilioWhatsAppChannel::class, $resolvedChannels['whatsapp'])
                        ->notify(new TwilioWhatsAppNotification($waText));
                });
            }

            foreach ($logs as $log) {
                $this->updateLogStatus($log, 'sent');
            }
        } catch (\Throwable $e) {
            foreach ($logs as $log) {
                $this->updateLogStatus($log, 'failed', $e->getMessage());
            }

            $this->tagSentry($templateKey, $recipient);

            Log::error('Notification dispatch failed', [
                'template' => $templateKey,
                'recipient' => $recipient?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function dispatchWithRateLimit(Client $recipient, string $channel, string $templateKey, callable $callback): void
    {
        $hourKey = "notif:{$recipient->id}:{$channel}:".now()->format('YmdH');
        $maxAttempts = $channel === 'sms' ? 5 : 10;

        if (RateLimiter::tooManyAttempts($hourKey, $maxAttempts)) {
            Log::warning('Notification rate limit exceeded', [
                'recipient' => $recipient->id,
                'channel' => $channel,
                'template' => $templateKey,
                'retry_after' => RateLimiter::availableIn($hourKey),
            ]);

            return;
        }

        RateLimiter::hit($hourKey, 3600);

        $callback();
    }

    private function resolveChannels(Client $client, array $preferred, string $templateKey): array
    {
        $critical = in_array($templateKey, ['booking.confirmation', 'payment.receipt']);

        $channels = [];

        if ($critical || in_array('mail', $preferred) || $client->preferred_channel === 'email') {
            $channels['mail'] = $client->email;
        }

        if (! $critical) {
            if (in_array('sms', $preferred) && $client->phone) {
                $channels['sms'] = $client->phone;
            }

            if (in_array('whatsapp', $preferred) && $client->phone) {
                $channels['whatsapp'] = $client->phone;
            }
        }

        if (empty($channels)) {
            $channels['mail'] = $client->email;
        }

        return $channels;
    }

    private function isQuietHours(): bool
    {
        $hour = (int) now()->format('G');

        return $hour >= self::QUIET_HOURS['start'] || $hour < self::QUIET_HOURS['end'];
    }

    private function createLogEntry(string $templateKey, Client $recipient, string $channel, string $address): NotificationsLog
    {
        return NotificationsLog::create([
            'recipient_type' => 'client',
            'recipient_id' => $recipient->id,
            'channel' => $channel,
            'template_key' => $templateKey,
            'status' => 'pending',
            'metadata' => ['address' => $address],
        ]);
    }

    private function updateLogStatus(NotificationsLog $log, string $status, ?string $reason = null): void
    {
        $log->status = $status;

        if ($status === 'sent') {
            $log->sent_at = now();
        } elseif ($status === 'failed') {
            $log->failed_at = now();
            $log->failure_reason = $reason;
        }

        $log->save();
    }

    private function tagSentry(string $templateKey, ?Client $recipient): void
    {
        if (! function_exists('sentry_configure_scope') || ! $recipient) {
            return;
        }

        sentry_configure_scope(function (Scope $scope) use ($templateKey, $recipient): void {
            $scope->setTag('notification.template', $templateKey);
            $scope->setTag('notification.recipient_type', 'client');
            $scope->setTag('notification.recipient_id', (string) $recipient->id);
        });
    }

    private function shortBookingUrl(Booking $booking): string
    {
        $locale = $booking->client?->preferred_locale ?? 'fr';
        $target = url("/{$locale}/portal/bookings/{$booking->reference}");

        $link = ShortLink::generate($target);

        return url("/s/{$link->hash}");
    }

    private function getChannelText(string $templateKey, string $channel, Client $recipient, array $data): string
    {
        $locale = $recipient->preferred_locale ?? 'fr';
        $booking = $data['booking'] ?? null;
        $url = $booking instanceof Booking ? $this->shortBookingUrl($booking) : '';

        return match ($templateKey) {
            'booking.confirmation' => $booking
                ? __("notifications.booking_confirmed.{$channel}", [
                    'reference' => $booking->reference,
                    'date' => $booking->starts_at->format('d/m/Y H:i'),
                    'url' => $url,
                ], $locale)
                : '',
            'booking.reminder.24h' => $booking
                ? __("notifications.booking_reminder_24h.{$channel}", [
                    'reference' => $booking->reference,
                    'date' => $booking->starts_at->format('d/m/Y H:i'),
                    'url' => $url,
                ], $locale)
                : '',
            'booking.reminder.1h' => $booking
                ? __("notifications.booking_reminder_1h.{$channel}", [
                    'reference' => $booking->reference,
                    'date' => $booking->starts_at->format('d/m/Y H:i'),
                    'url' => $url,
                ], $locale)
                : '',
            'booking.cancelled' => $booking
                ? __("notifications.booking_cancelled.{$channel}", [
                    'reference' => $booking->reference,
                    'url' => $url,
                ], $locale)
                : '',
            'booking.rescheduled' => __("notifications.booking_rescheduled.{$channel}", [
                'old_ref' => $data['old_booking']?->reference ?? '',
                'new_ref' => $data['new_booking']?->reference ?? '',
                'date' => $data['new_booking']?->starts_at?->format('d/m/Y H:i') ?? '',
                'url' => $data['new_booking'] instanceof Booking ? $this->shortBookingUrl($data['new_booking']) : '',
            ], $locale),
            'payment.receipt' => __("notifications.payment_receipt.{$channel}", [
                'number' => $data['receipt']?->number ?? '',
                'amount' => isset($data['receipt']) ? number_format($data['receipt']->amount_centimes / 100, 2, ',', ' ') : '',
                'url' => '',
            ], $locale),
            'payment.failed' => $booking
                ? __("notifications.payment_failed.{$channel}", [
                    'reference' => $booking->reference,
                    'url' => $url,
                ], $locale)
                : '',
            'refund.issued' => __("notifications.refund_issued.{$channel}", [
                'amount' => isset($data['refund']) ? number_format($data['refund']->amount_centimes / 100, 2, ',', ' ') : '',
                'url' => '',
            ], $locale),
            default => '',
        };
    }
}
