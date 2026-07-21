<?php

namespace App\Enums;

enum SettingGroup: string
{
    case GENERAL = 'general';
    case BUSINESS = 'business';
    case COMMERCE = 'commerce';
    case ORDERS = 'orders';
    case PAYMENTS = 'payments';
    case INVENTORY = 'inventory';
    case NOTIFICATIONS = 'notifications';
    case EMAIL = 'email';
    case LOCALIZATION = 'localization';
    case SECURITY = 'security';
    case INTEGRATIONS = 'integrations';
    case SYSTEM = 'system';
    case SOCIAL = 'social';
    case CONTENT = 'content';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::BUSINESS => 'Business',
            self::COMMERCE => 'Commerce',
            self::ORDERS => 'Orders',
            self::PAYMENTS => 'Payments',
            self::INVENTORY => 'Inventory',
            self::NOTIFICATIONS => 'Notifications',
            self::EMAIL => 'Email',
            self::LOCALIZATION => 'Localization',
            self::SECURITY => 'Security',
            self::INTEGRATIONS => 'Integrations',
            self::SYSTEM => 'System',
            self::SOCIAL => 'Social',
            self::CONTENT => 'Content',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GENERAL => 'heroicon-o-cog-6-tooth',
            self::BUSINESS => 'heroicon-o-building-office',
            self::COMMERCE => 'heroicon-o-shopping-bag',
            self::ORDERS => 'heroicon-o-clipboard-document-list',
            self::PAYMENTS => 'heroicon-o-credit-card',
            self::INVENTORY => 'heroicon-o-cube',
            self::NOTIFICATIONS => 'heroicon-o-bell',
            self::EMAIL => 'heroicon-o-envelope',
            self::LOCALIZATION => 'heroicon-o-globe-alt',
            self::SECURITY => 'heroicon-o-shield-check',
            self::INTEGRATIONS => 'heroicon-o-puzzle-piece',
            self::SYSTEM => 'heroicon-o-server',
            self::SOCIAL => 'heroicon-o-share',
            self::CONTENT => 'heroicon-o-document-text',
        };
    }
}
