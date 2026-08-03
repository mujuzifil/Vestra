<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case CRM = 'crm';
    case SALES = 'sales';
    case DISTRIBUTOR = 'distributor';
    case CUSTOMER = 'customer';
    case OPERATIONS = 'operations';
    case MARKETING = 'marketing';
    case SYSTEM = 'system';
    case SECURITY = 'security';

    public function label(): string
    {
        return match ($this) {
            self::CRM => 'CRM',
            self::SALES => 'Sales',
            self::DISTRIBUTOR => 'Distributor',
            self::CUSTOMER => 'Customer',
            self::OPERATIONS => 'Operations',
            self::MARKETING => 'Marketing',
            self::SYSTEM => 'System',
            self::SECURITY => 'Security',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CRM => 'heroicon-o-user-group',
            self::SALES => 'heroicon-o-currency-dollar',
            self::DISTRIBUTOR => 'heroicon-o-truck',
            self::CUSTOMER => 'heroicon-o-users',
            self::OPERATIONS => 'heroicon-o-cog-8-tooth',
            self::MARKETING => 'heroicon-o-megaphone',
            self::SYSTEM => 'heroicon-o-bell',
            self::SECURITY => 'heroicon-o-shield-exclamation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CRM => 'info',
            self::SALES => 'success',
            self::DISTRIBUTOR => 'warning',
            self::CUSTOMER => 'info',
            self::OPERATIONS => 'gray',
            self::MARKETING => 'purple',
            self::SYSTEM => 'primary',
            self::SECURITY => 'danger',
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
