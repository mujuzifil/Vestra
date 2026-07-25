<?php

namespace App\Enums;

enum CustomerNoteType: string
{
    case GENERAL = 'general';
    case SUPPORT = 'support';
    case SALES = 'sales';
    case INTERNAL = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::SUPPORT => 'Support',
            self::SALES => 'Sales',
            self::INTERNAL => 'Internal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GENERAL => 'gray',
            self::SUPPORT => 'info',
            self::SALES => 'success',
            self::INTERNAL => 'warning',
        };
    }
}
