<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'client_id',
        'consultation_plan_id',
        'service_category',
        'description',
        'format',
        'starts_at',
        'ends_at',
        'status',
        'meeting_url',
        'total_centimes',
        'currency',
        'cancellation_reason',
        'cancelled_at',
        'completed_at',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'internal_notes' => 'encrypted',
            'total_centimes' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ConsultationPlan::class, 'consultation_plan_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }
}
