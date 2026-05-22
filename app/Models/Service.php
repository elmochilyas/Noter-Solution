<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title_translations',
        'intro_translations',
        'body_translations',
        'transactions_translations',
        'required_documents_translations',
        'icon',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title_translations' => 'array',
            'intro_translations' => 'array',
            'body_translations' => 'array',
            'transactions_translations' => 'array',
            'required_documents_translations' => 'array',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
