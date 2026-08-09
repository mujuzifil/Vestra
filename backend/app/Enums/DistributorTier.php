<?php

namespace App\Enums;

enum DistributorTier: string
{
    case SILVER = 'silver';
    case GOLD = 'gold';
    case MASTER = 'master';

    public function label(): string
    {
        return match ($this) {
            self::SILVER => 'Silver Distributor',
            self::GOLD => 'Gold Distributor',
            self::MASTER => 'Master Distributor',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::SILVER => 'Silver',
            self::GOLD => 'Gold',
            self::MASTER => 'Master',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SILVER => 'gray',
            self::GOLD => 'warning',
            self::MASTER => 'success',
        };
    }
}
