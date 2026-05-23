<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'retrieved_faq_ids',
        'tokens_in',
        'tokens_out',
        'latency_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'retrieved_faq_ids' => 'array',
            'tokens_in' => 'integer',
            'tokens_out' => 'integer',
            'latency_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }
}
