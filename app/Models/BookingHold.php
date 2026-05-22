<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHold extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'slot_starts_at',
        'slot_ends_at',
        'client_id',
        'session_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'slot_starts_at' => 'datetime',
            'slot_ends_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
