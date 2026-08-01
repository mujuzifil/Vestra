<?php

namespace App\Enums;

enum QuoteRequestStatus: string
{
    case PENDING = 'pending';
    case CONTACTED = 'contacted';
    case QUOTED = 'quoted';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONTACTED => 'Contacted',
            self::QUOTED => 'Quoted',
            self::APPROVED => 'Approved',
            self::DECLINED => 'Declined',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONTACTED => 'info',
            self::QUOTED => 'primary',
            self::APPROVED => 'success',
            self::DECLINED => 'danger',
            self::CLOSED => 'gray',
        };
    }
}
