<?php

use App\Models\Client;
use App\Models\NotificationsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notifications log can be created and updated', function () {
    $client = Client::factory()->create();

    $log = NotificationsLog::create([
        'recipient_type' => 'client',
        'recipient_id' => $client->id,
        'channel' => 'mail',
        'template_key' => 'booking.confirmation',
        'status' => 'pending',
        'metadata' => ['address' => $client->email],
    ]);

    expect($log->id)->not->toBeNull();
    expect($log->status)->toBe('pending');

    $log->update([
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    expect($log->refresh()->status)->toBe('sent');
    expect($log->sent_at)->not->toBeNull();
});

test('notifications log records failures', function () {
    $client = Client::factory()->create();

    $log = NotificationsLog::create([
        'recipient_type' => 'client',
        'recipient_id' => $client->id,
        'channel' => 'mail',
        'template_key' => 'booking.confirmation',
        'status' => 'failed',
        'failure_reason' => 'Connection timeout',
        'failed_at' => now(),
    ]);

    expect($log->status)->toBe('failed');
    expect($log->failure_reason)->toBe('Connection timeout');
});
