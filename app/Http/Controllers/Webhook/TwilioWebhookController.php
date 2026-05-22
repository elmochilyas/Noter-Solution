<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\NotificationsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $to = $request->input('To');
        $from = $request->input('From');

        if (! $messageSid || ! $messageStatus) {
            return response('Missing MessageSid or MessageStatus', 400);
        }

        $statusMap = [
            'queued' => 'pending',
            'sent' => 'sent',
            'delivered' => 'delivered',
            'undelivered' => 'failed',
            'failed' => 'failed',
        ];

        $status = $statusMap[$messageStatus] ?? null;

        if (! $status) {
            return response('Unhandled status', 200);
        }

        $channel = $from && str_starts_with($from, 'whatsapp:') ? 'whatsapp' : 'sms';

        $log = NotificationsLog::where('provider_message_id', $messageSid)->first();

        if (! $log) {
            Log::info('Twilio webhook: no matching log entry', [
                'message_sid' => $messageSid,
                'status' => $messageStatus,
                'channel' => $channel,
            ]);

            return response('No matching log entry', 200);
        }

        $log->status = $status;

        if ($status === 'delivered') {
            $log->delivered_at = now();
        } elseif ($status === 'failed') {
            $log->failed_at = now();
            $log->failure_reason = $request->input('ErrorCode', 'Unknown error');
        }

        $log->save();

        Log::info('Twilio webhook processed', [
            'message_sid' => $messageSid,
            'status' => $messageStatus,
            'channel' => $channel,
        ]);

        return response('OK', 200);
    }
}
