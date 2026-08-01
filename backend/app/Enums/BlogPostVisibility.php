<?php

namespace App\Enums;

enum BlogPostVisibility: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::INTERNAL => 'Internal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLIC => 'success',
            self::INTERNAL => 'warning',
        };
    }
}
