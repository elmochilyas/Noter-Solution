<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'booking_id',
        'payment_id',
        'amount_centimes',
        'vat_centimes',
        'storage_path',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_centimes' => 'integer',
            'vat_centimes' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
