<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case SUCCESS = 'success';
    case INFORMATION = 'information';
    case WARNING = 'warning';
    case ERROR = 'error';

    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::INFORMATION => 'Information',
            self::WARNING => 'Warning',
            self::ERROR => 'Error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SUCCESS => 'heroicon-o-check-circle',
            self::INFORMATION => 'heroicon-o-information-circle',
            self::WARNING => 'heroicon-o-exclamation-triangle',
            self::ERROR => 'heroicon-o-x-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUCCESS => 'success',
            self::INFORMATION => 'info',
            self::WARNING => 'warning',
            self::ERROR => 'danger',
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
