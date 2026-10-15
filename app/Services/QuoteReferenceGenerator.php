<?php

namespace App\Services;

use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;

/**
 * Generates references like CM-Q-2026-0001. The year follows the Nairobi clock and the
 * sequence restarts every year. Call inside a DB transaction: a PostgreSQL advisory lock
 * serialises concurrent submissions until commit, and the UNIQUE index on
 * quote_requests.reference_number is the final safety net.
 */
class QuoteReferenceGenerator
{
    private const LOCK_KEY = 7353001;

    public function next(): string
    {
        DB::select('select pg_advisory_xact_lock(?)', [self::LOCK_KEY]);

        $prefix = $this->prefix();

        $row = QuoteRequest::query()
            ->where('reference_number', 'like', $prefix.'%')
            ->selectRaw("max(cast(split_part(reference_number, '-', 4) as integer)) as max_sequence")
            ->first();

        $next = ((int) ($row->max_sequence ?? 0)) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /** A well-formed but non-existent reference, returned to bots that trip the honeypot. */
    public function decoy(): string
    {
        return $this->prefix().'0000';
    }

    private function prefix(): string
    {
        $year = now(config('crystalmovers.timezone'))->year;

        return config('crystalmovers.quote_reference_prefix')."-{$year}-";
    }
}
