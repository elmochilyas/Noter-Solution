<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'email',
        'phone',
        'full_name',
        'preferred_locale',
        'preferred_channel',
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

    public function freeOrientationCountInDays(int $days = 90): int
    {
        $cutoff = now()->subDays($days);

        return $this->bookings()
            ->where('status', 'confirmed')
            ->where('total_centimes', 0)
            ->where('created_at', '>=', $cutoff)
            ->count();
    }

    public function hasExceededFreeOrientationLimit(int $limit = 2, int $days = 90): bool
    {
        return $this->freeOrientationCountInDays($days) >= $limit;
    }

    public function rescheduleCountInDays(int $days = 30): int
    {
        $cutoff = now()->subDays($days);

        return $this->bookings()
            ->where('cancellation_reason', 'rescheduled')
            ->where('cancelled_at', '>=', $cutoff)
            ->count();
    }

    public function hasExceededRescheduleLimit(int $limit = 2, int $days = 30): bool
    {
        return $this->rescheduleCountInDays($days) >= $limit;
    }
}
