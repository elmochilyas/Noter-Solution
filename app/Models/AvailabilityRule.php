<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'starts_at',
        'ends_at',
        'format',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'starts_at' => 'string',
            'ends_at' => 'string',
            'is_active' => 'boolean',
        ];
    }
}
