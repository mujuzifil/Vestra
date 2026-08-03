<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case INFORMATION = 'information';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::INFORMATION => 'Information',
            self::SUCCESS => 'Success',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INFORMATION => 'heroicon-o-information-circle',
            self::SUCCESS => 'heroicon-o-check-circle',
            self::WARNING => 'heroicon-o-exclamation-triangle',
            self::CRITICAL => 'heroicon-o-shield-exclamation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INFORMATION => 'info',
            self::SUCCESS => 'success',
            self::WARNING => 'warning',
            self::CRITICAL => 'danger',
        };
    }

    public function sortWeight(): int
    {
        return match ($this) {
            self::CRITICAL => 4,
            self::WARNING => 3,
            self::SUCCESS => 2,
            self::INFORMATION => 1,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function filterOptions(): array
    {
        return self::cases();
    }

    public static function tryFromString(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        return self::tryFrom($value);
    }
}
