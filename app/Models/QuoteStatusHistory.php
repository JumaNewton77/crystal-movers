<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteStatusHistory extends Model
{
    protected $table = 'quote_status_history';

    // Only created_at exists (the row is an immutable log entry).
    public $timestamps = false;

    protected $fillable = ['quote_request_id', 'from_status', 'to_status', 'changed_by', 'note'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
