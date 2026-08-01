<?php

namespace App\Enums;

enum BlogPostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SCHEDULED = 'scheduled';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::SCHEDULED => 'Scheduled',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
            self::SCHEDULED => 'warning',
            self::ARCHIVED => 'danger',
        };
    }
}
