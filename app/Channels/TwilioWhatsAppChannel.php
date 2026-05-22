<?php

namespace App\Channels;

use App\Models\NotificationsLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $to = $notifiable->phone ?? $notifiable->routeNotificationFor('whatsapp', $notification);

        if (! $to) {
            return;
        }

        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.from_whatsapp');

        if (! $accountSid || ! $authToken || ! $from) {
            Log::info('Twilio WhatsApp not configured — would send WhatsApp', [
                'to' => $to,
                'notification' => get_class($notification),
            ]);

            return;
        }

        $text = method_exists($notification, 'toTwilioWhatsApp')
            ? $notification->toTwilioWhatsApp($notifiable)
            : null;

        if (! $text) {
            return;
        }

        try {
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $from,
                    'Body' => $text,
                    'To' => "whatsapp:{$to}",
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $messageSid = $body['sid'] ?? null;

                NotificationsLog::where('channel', 'whatsapp')
                    ->where('metadata->address', $to)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()?->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'provider_message_id' => $messageSid,
                    ]);

                Log::info('Twilio WhatsApp sent', [
                    'to' => $to,
                    'sid' => $messageSid,
                ]);
            } else {
                Log::error('Twilio WhatsApp failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                NotificationsLog::where('channel', 'whatsapp')
                    ->where('metadata->address', $to)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()?->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'failure_reason' => $response->body(),
                    ]);
            }
        } catch (\Throwable $e) {
            Log::error('Twilio WhatsApp exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
