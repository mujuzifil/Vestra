<?php

namespace App\Enums;

enum DistributorChannel: string
{
    case RETAIL = 'retail';
    case DISTRIBUTOR = 'distributor';

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Retail',
            self::DISTRIBUTOR => 'Distributor',
        };
    }
}
