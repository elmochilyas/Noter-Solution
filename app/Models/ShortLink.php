<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    protected $fillable = [
        'hash',
        'target_url',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public static function generate(string $targetUrl, ?\DateTimeInterface $expiresAt = null): self
    {
        $hash = Str::random(7);

        while (self::where('hash', $hash)->exists()) {
            $hash = Str::random(7);
        }

        return self::create([
            'hash' => $hash,
            'target_url' => $targetUrl,
            'expires_at' => $expiresAt ?? now()->addDays(90),
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        });
    }
}
