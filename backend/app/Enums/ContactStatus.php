<?php

namespace App\Enums;

enum ContactStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED => 'Resolved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'primary',
            self::IN_PROGRESS => 'warning',
            self::RESOLVED => 'success',
        };
    }
}
