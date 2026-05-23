<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachedResult extends Model
{
    protected $table = 'cached_results';

    protected $fillable = [
        'key',
        'value',
        'ttl_seconds',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
            'ttl_seconds' => 'integer',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        if (! $this->ttl_seconds || ! $this->updated_at) {
            return false;
        }

        return $this->updated_at->addSeconds($this->ttl_seconds)->isPast();
    }
}
