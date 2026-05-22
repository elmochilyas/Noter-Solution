<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question_translations',
        'answer_translations',
        'is_published',
        'display_order',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'question_translations' => 'array',
            'answer_translations' => 'array',
            'is_published' => 'boolean',
            'display_order' => 'integer',
            'view_count' => 'integer',
        ];
    }
}
