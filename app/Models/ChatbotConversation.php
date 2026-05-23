<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'session_id',
        'client_id',
        'locale',
        'metadata',
        'intent_resolved',
        'led_to_booking_id',
        'started_at',
        'last_message_at',
        'ended_at',
        'is_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'last_message_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_reviewed' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }

    public function ledToBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'led_to_booking_id');
    }

    public function getPurgeAfterAttribute(): ?\DateTimeInterface
    {
        return $this->started_at?->addMonths(18);
    }

    public function scopeShouldPurge(Builder $query): Builder
    {
        return $query->where('started_at', '<', now()->subMonths(18));
    }
}
