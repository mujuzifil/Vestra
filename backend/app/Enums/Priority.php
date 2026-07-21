<?php

namespace App\Enums;

enum Priority: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
    case NEUTRAL = 'neutral';

    public function label(): string
    {
        return match ($this) {
            self::CRITICAL => 'Critical',
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
            self::NEUTRAL => 'Neutral',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CRITICAL => 'danger',
            self::HIGH => 'warning',
            self::MEDIUM => 'primary',
            self::LOW => 'info',
            self::NEUTRAL => 'gray',
        };
    }
}
