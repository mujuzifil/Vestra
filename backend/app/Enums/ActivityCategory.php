<?php

namespace App\Enums;

enum ActivityCategory: string
{
    case AUTHENTICATION = 'authentication';
    case SALES = 'sales';
    case CRM = 'crm';
    case DISTRIBUTORS = 'distributors';
    case SUPPORT = 'support';
    case PRODUCTS = 'products';
    case MARKETING = 'marketing';
    case ADMINISTRATION = 'administration';
    case SYSTEM = 'system';
    case SECURITY = 'security';

    public function label(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'Authentication',
            self::SALES => 'Sales',
            self::CRM => 'CRM',
            self::DISTRIBUTORS => 'Distributors',
            self::SUPPORT => 'Support',
            self::PRODUCTS => 'Products',
            self::MARKETING => 'Marketing',
            self::ADMINISTRATION => 'Administration',
            self::SYSTEM => 'System',
            self::SECURITY => 'Security',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'heroicon-o-key',
            self::SALES => 'heroicon-o-currency-dollar',
            self::CRM => 'heroicon-o-users',
            self::DISTRIBUTORS => 'heroicon-o-truck',
            self::SUPPORT => 'heroicon-o-ticket',
            self::PRODUCTS => 'heroicon-o-cube',
            self::MARKETING => 'heroicon-o-megaphone',
            self::ADMINISTRATION => 'heroicon-o-building-office',
            self::SYSTEM => 'heroicon-o-cog-6-tooth',
            self::SECURITY => 'heroicon-o-shield-exclamation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'info',
            self::SALES => 'success',
            self::CRM => 'info',
            self::DISTRIBUTORS => 'warning',
            self::SUPPORT => 'primary',
            self::PRODUCTS => 'info',
            self::MARKETING => 'info',
            self::ADMINISTRATION => 'gray',
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
