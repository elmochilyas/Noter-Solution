<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\NotificationsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResendWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->all();

        $eventType = $payload['type'] ?? null;
        $emailId = $payload['data']['email_id'] ?? null;

        if (! $eventType || ! $emailId) {
            return response('Missing event type or email_id', 400);
        }

        $statusMap = [
            'email.sent' => 'sent',
            'email.delivered' => 'delivered',
            'email.bounced' => 'failed',
            'email.complained' => 'failed',
        ];

        $status = $statusMap[$eventType] ?? null;

        if (! $status) {
            return response('Unhandled event type', 200);
        }

        $log = NotificationsLog::where('provider_message_id', $emailId)->first();

        if (! $log) {
            Log::info('Resend webhook: no matching log entry', [
                'email_id' => $emailId,
                'event' => $eventType,
            ]);

            return response('No matching log entry', 200);
        }

        $log->status = $status;

        if ($status === 'delivered') {
            $log->delivered_at = now();
        } elseif ($status === 'failed') {
            $log->failed_at = now();
            $log->failure_reason = $payload['data']['bounce']['reason'] ?? $payload['data']['complaint']['reason'] ?? 'Bounced';
        }

        $log->save();

        Log::info('Resend webhook processed', [
            'email_id' => $emailId,
            'event' => $eventType,
            'status' => $status,
        ]);

        return response('OK', 200);
    }
}
