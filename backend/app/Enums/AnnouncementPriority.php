<?php

namespace App\Enums;

enum AnnouncementPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => 'gray',
            self::NORMAL => 'info',
            self::HIGH => 'warning',
            self::CRITICAL => 'danger',
        };
    }
}
