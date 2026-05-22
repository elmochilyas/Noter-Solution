<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'email',
        'phone',
        'full_name',
        'preferred_locale',
        'national_id',
        'national_id_last4',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'national_id' => 'encrypted',
            'last_login_at' => 'datetime',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
