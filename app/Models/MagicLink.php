<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'token_hash',
        'expires_at',
        'consumed_at',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }
}
