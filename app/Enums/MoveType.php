<?php

namespace App\Enums;

/**
 * Phase 1 move types. International moving is intentionally absent
 * (it is a "coming soon" display-only service).
 */
enum MoveType: string
{
    case HomeMoving = 'home_moving';
    case OfficeRelocation = 'office_relocation';
    case PackingService = 'packing_service';
    case LocalMoving = 'local_moving';
    case InterCountyMoving = 'inter_county_moving';

    public function label(): string
    {
        return match ($this) {
            self::HomeMoving => 'Home Moving',
            self::OfficeRelocation => 'Office Relocation',
            self::PackingService => 'Packing Service',
            self::LocalMoving => 'Local Moving',
            self::InterCountyMoving => 'Inter-county Moving',
        };
    }
}
