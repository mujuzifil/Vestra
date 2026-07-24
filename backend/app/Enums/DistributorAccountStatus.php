<?php

namespace App\Enums;

enum DistributorAccountStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SUSPENDED => 'danger',
        };
    }
}
