<?php

namespace App\Filament\Pages\Settings;

use App\Enums\SettingGroup;
use Filament\Pages\Page;

class SettingsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.settings.settings-dashboard';

    public function getTitle(): string
    {
        return 'Platform Configuration';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<int, SettingGroup>
     */
    public function getSettingGroups(): array
    {
        return [
            SettingGroup::SYSTEM,
        ];
    }

    public function getGroupDescription(SettingGroup $group): string
    {
        return match ($group) {
            SettingGroup::GENERAL => 'Application name, logo, contact details, and regional defaults.',
            SettingGroup::BUSINESS => 'Registration numbers, invoice prefixes, and business hours.',
            SettingGroup::COMMERCE => 'Product defaults, stock thresholds, and tax display.',
            SettingGroup::ORDERS => 'Order prefixes, statuses, and cancellation rules.',
            SettingGroup::PAYMENTS => 'Payment methods, timeouts, and offline instructions.',
            SettingGroup::INVENTORY => 'Low stock behaviour, SKU format, and alerts.',
            SettingGroup::NOTIFICATIONS => 'Administrator, customer, and distributor notifications.',
            SettingGroup::EMAIL => 'SMTP configuration and sender identity.',
            SettingGroup::LOCALIZATION => 'Language, timezone, date, and currency formatting.',
            SettingGroup::SECURITY => 'Password policy, login limits, and session timeout.',
            SettingGroup::INTEGRATIONS => 'Payment gateways and third-party services.',
            SettingGroup::SYSTEM => 'Maintenance mode, debug mode, and system information.',
            default => 'Manage configuration values.',
        };
    }

    public function getGroupRoute(SettingGroup $group): string
    {
        return match ($group) {
            SettingGroup::SYSTEM => SystemInformation::getUrl(),
            default => '#',
        };
    }

    public function getGroupColor(SettingGroup $group): string
    {
        return match ($group) {
            SettingGroup::GENERAL => 'primary',
            SettingGroup::BUSINESS => 'info',
            SettingGroup::COMMERCE => 'success',
            SettingGroup::ORDERS => 'warning',
            SettingGroup::PAYMENTS => 'primary',
            SettingGroup::INVENTORY => 'info',
            SettingGroup::NOTIFICATIONS => 'warning',
            SettingGroup::EMAIL => 'danger',
            SettingGroup::LOCALIZATION => 'success',
            SettingGroup::SECURITY => 'danger',
            SettingGroup::INTEGRATIONS => 'primary',
            SettingGroup::SYSTEM => 'gray',
            default => 'primary',
        };
    }
}
