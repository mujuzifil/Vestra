<?php

namespace App\Filament\Pages\Administration;

use App\Enums\SettingGroup;
use App\Models\Setting;
use App\Services\AuditService;
use App\Services\SettingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SecurityPolicies extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Security Policies';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.administration.security-policies';

    public ?array $data = [];

    /** @var \Illuminate\Support\Collection<int, Setting> */
    protected $settings;

    public function getTitle(): string|Htmlable
    {
        return 'Security Policies';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->settings = app(SettingService::class)->group(SettingGroup::SECURITY);
        $state = [];

        foreach ($this->settings as $setting) {
            $state[$setting->key] = $setting->typedValue();
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema($this->buildSchema());
    }

    protected function buildSchema(): array
    {
        $this->settings ??= app(SettingService::class)->group(SettingGroup::SECURITY);

        $sections = [];

        foreach ($this->settings as $setting) {
            $sections[] = Section::make($setting->label)
                ->description($setting->description)
                ->aside()
                ->schema([$this->buildField($setting)]);
        }

        return $sections;
    }

    protected function buildField(Setting $setting): mixed
    {
        $key = $setting->key;

        return match ($setting->type) {
            \App\Enums\SettingType::NUMBER => TextInput::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->numeric()
                ->autocomplete(false),

            \App\Enums\SettingType::BOOLEAN => Toggle::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->onIcon('heroicon-m-check')
                ->offIcon('heroicon-m-x-mark'),

            default => TextInput::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->disabled(),
        };
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->action('save'),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->url(fn (): string => AdministrationDashboard::getUrl())
                ->outlined(),
        ];
    }

    public function save(): void
    {
        $service = app(SettingService::class);
        $this->settings ??= $service->group(SettingGroup::SECURITY);
        $state = $this->form->getState();

        foreach ($this->settings as $setting) {
            $newValue = $state[$setting->key] ?? null;
            $previousValue = $setting->value;

            if ($setting->type === \App\Enums\SettingType::BOOLEAN) {
                $newValue = $newValue ? '1' : '0';
            }

            $normalised = $newValue !== null ? (string) $newValue : null;

            if ($normalised !== $previousValue) {
                $service->set($setting->key, $newValue);

                AuditService::log(
                    auth()->user(),
                    'security_policy.updated',
                    $setting,
                    [
                        'key' => $setting->key,
                        'previous_value' => $previousValue,
                        'new_value' => $normalised,
                    ]
                );
            }
        }

        Notification::make()
            ->title('Security policies saved')
            ->body('Security policy settings have been updated.')
            ->success()
            ->send();

        $this->redirect(SecurityPolicies::getUrl(), navigate: true);
    }
}
