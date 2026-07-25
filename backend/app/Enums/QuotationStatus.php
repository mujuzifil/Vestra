<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case REVIEWED = 'reviewed';
    case QUOTED = 'quoted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CONVERTED_TO_ORDER = 'converted_to_order';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::REVIEWED => 'Reviewed',
            self::QUOTED => 'Quoted',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::CONVERTED_TO_ORDER => 'Converted to Order',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SUBMITTED => 'warning',
            self::REVIEWED => 'info',
            self::QUOTED => 'primary',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::CONVERTED_TO_ORDER => 'success',
        };
    }
}
