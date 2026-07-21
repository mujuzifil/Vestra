<?php

namespace App\Filament\Pages\Settings;

use App\Enums\SettingGroup;
use App\Filament\Resources\SettingResource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Redirect;

class SettingsDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings.settings-dashboard';

    public ?string $search = '';

    public function getTitle(): string
    {
        return 'Platform Configuration';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Search settings')
                    ->placeholder('Search by keyword, label, or group...')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(debounce: 300)
                    ->extraAlpineAttributes([
                        '@keydown.enter' => '$wire.searchSettings()',
                    ]),
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function searchSettings(): void
    {
        $term = $this->data['search'] ?? '';

        if (blank($term)) {
            return;
        }

        $this->redirect(SettingResource::getUrl('index', ['tableSearch' => $term]));
    }

    public function getSettingGroups(): array
    {
        return [
            SettingGroup::GENERAL,
            SettingGroup::BUSINESS,
            SettingGroup::COMMERCE,
            SettingGroup::ORDERS,
            SettingGroup::PAYMENTS,
            SettingGroup::INVENTORY,
            SettingGroup::NOTIFICATIONS,
            SettingGroup::EMAIL,
            SettingGroup::LOCALIZATION,
            SettingGroup::SECURITY,
            SettingGroup::INTEGRATIONS,
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
            default => SettingResource::getUrl("edit-{$group->value}"),
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
