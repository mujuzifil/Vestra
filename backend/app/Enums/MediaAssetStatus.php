<?php

namespace App\Enums;

enum MediaAssetStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
        };
    }
}
