<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_translations',
        'description_translations',
        'included_features',
        'duration_minutes',
        'price_centimes',
        'format',
        'is_recommended',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'name_translations' => 'array',
            'description_translations' => 'array',
            'included_features' => 'array',
            'duration_minutes' => 'integer',
            'price_centimes' => 'integer',
            'is_recommended' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }
}
