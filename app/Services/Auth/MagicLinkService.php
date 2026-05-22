<?php

namespace App\Services\Auth;

use App\Events\MagicLinkRequested;
use App\Models\Client;
use App\Models\MagicLink;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final readonly class MagicLinkService
{
    public function __construct(
        private int $expiryMinutes = 15,
    ) {}

    public function send(Client $client, string $locale, ?string $intendedUrl = null): MagicLink
    {
        $token = Str::random(64);

        $magicLink = MagicLink::create([
            'client_id' => $client->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes($this->expiryMinutes),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'portal.login.verify',
            now()->addMinutes($this->expiryMinutes),
            ['token' => $token, 'email' => $client->email, 'locale' => $locale],
        );

        MagicLinkRequested::dispatch($client, $signedUrl, $intendedUrl);

        return $magicLink;
    }

    public function verify(string $email, string $token): ?Client
    {
        $tokenHash = hash('sha256', $token);

        $magicLink = MagicLink::where('token_hash', $tokenHash)
            ->whereHas('client', fn ($q) => $q->where('email', $email))
            ->first();

        if (! $magicLink || ! $magicLink->isValid()) {
            return null;
        }

        $magicLink->update(['consumed_at' => now()]);

        return $magicLink->client;
    }
}
