<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'booking_id',
        'client_id',
        'original_filename',
        'mime_type',
        'size_bytes',
        'storage_path',
        'scan_status',
        'scanned_at',
        'purge_after',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'size_bytes' => 'integer',
            'scanned_at' => 'datetime',
            'purge_after' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
