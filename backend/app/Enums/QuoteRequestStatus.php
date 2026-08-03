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

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONTACTED => 'heroicon-o-phone',
            self::QUOTED => 'heroicon-o-document-text',
            self::APPROVED => 'heroicon-o-check-circle',
            self::DECLINED => 'heroicon-o-x-circle',
            self::CLOSED => 'heroicon-o-archive-box',
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
