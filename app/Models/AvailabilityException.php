<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityException extends Model
{
    use HasFactory;

    protected $fillable = [
        'starts_at',
        'ends_at',
        'reason',
        'is_holiday',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_holiday' => 'boolean',
        ];
    }
}
