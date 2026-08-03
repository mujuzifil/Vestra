<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case PROSPECT = 'prospect';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::PROSPECT => 'Prospect',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PROSPECT => 'heroicon-o-star',
            self::ACTIVE => 'heroicon-o-check-circle',
            self::INACTIVE => 'heroicon-o-minus-circle',
            self::SUSPENDED => 'heroicon-o-no-symbol',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROSPECT => 'warning',
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'danger',
        };
    }
}
