<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Assessment = 'assessment';
    case QuotationSent = 'quotation_sent';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Assessment => 'Assessment/Discussion',
            self::QuotationSent => 'Quotation Sent',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
