<?php

namespace App\Models;

use App\Enums\MoveType;
use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $fillable = [
        'reference_number', 'name', 'phone', 'email', 'service_id', 'move_type', 'from_location',
        'to_location', 'location', 'preferred_date', 'preferred_time', 'message', 'details',
        'contact_consent', 'status', 'admin_notes', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'move_type' => MoveType::class,
            'status' => QuoteStatus::class,
            'preferred_date' => 'date',
            'details' => 'array',
            'contact_consent' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(QuoteAttachment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(QuoteStatusHistory::class)->latest('created_at');
    }
}
