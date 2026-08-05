<?php

namespace App\Enums;

enum MediaAssetType: string
{
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case VIDEO = 'video';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'Images',
            self::DOCUMENT => 'Documents',
            self::VIDEO => 'Videos',
            self::OTHER => 'Other',
        };
    }
}
