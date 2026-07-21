<?php

namespace App\Enums;

enum FeedbackCategory: string
{
    case GENERAL = 'general';
    case BUG = 'bug';
    case FEATURE = 'feature';
    case COMPLAINT = 'complaint';
    case PRAISE = 'praise';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General Feedback',
            self::BUG => 'Bug Report',
            self::FEATURE => 'Feature Request',
            self::COMPLAINT => 'Complaint',
            self::PRAISE => 'Praise',
        };
    }
}
