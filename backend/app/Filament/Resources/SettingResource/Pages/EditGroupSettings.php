<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use App\Services\AuditService;
use App\Services\SettingService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

abstract class EditGroupSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.settings.pages.edit-group-settings';

    public ?array $data = [];

    /** @var Collection<int, Setting> */
    protected $settings;

    abstract public function getGroup(): SettingGroup;

    public function getTitle(): string|Htmlable
    {
        return $this->getGroup()->label().' Settings';
    }

    public function mount(): void
    {
        $this->settings = app(SettingService::class)->group($this->getGroup());
        $state = [];

        foreach ($this->settings as $setting) {
            $value = $setting->typedValue();

            if ($setting->isSensitive() && filled($value)) {
                $state[$setting->key] = Setting::ENCRYPTED_PLACEHOLDER;
            } elseif ($setting->type === SettingType::IMAGE) {
                $state[$setting->key] = $value ? [$value] : [];
            } elseif ($setting->type === SettingType::JSON) {
                $state[$setting->key] = is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT);
            } else {
                $state[$setting->key] = $value;
            }
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
        $this->settings ??= app(SettingService::class)->group($this->getGroup());

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

        if ($setting->isSensitive()) {
            return TextInput::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->password()
                ->revealable()
                ->autocomplete(false)
                ->placeholder('Enter new value to replace the stored secret')
                ->maxLength(65535);
        }

        return match ($setting->type) {
            SettingType::STRING => TextInput::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->maxLength(65535)
                ->autocomplete(false),

            SettingType::TEXT => Textarea::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->rows(4)
                ->columnSpanFull(),

            SettingType::NUMBER => TextInput::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->numeric()
                ->autocomplete(false),

            SettingType::BOOLEAN => Toggle::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->onIcon('heroicon-m-check')
                ->offIcon('heroicon-m-x-mark'),

            SettingType::JSON => Textarea::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->rows(6)
                ->columnSpanFull()
                ->extraInputAttributes(['class' => 'font-mono'])
                ->hint('Valid JSON required'),

            SettingType::SELECT => Select::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->options($setting->selectOptions())
                ->native(false),

            SettingType::IMAGE => FileUpload::make($key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->image()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                ->maxSize(2048)
                ->multiple(false)
                ->maxFiles(1)
                ->columnSpanFull()
                ->directory('settings-temp'),

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
                ->url(fn (): string => SettingResource::getUrl())
                ->outlined(),
        ];
    }

    public function save(): void
    {
        $service = app(SettingService::class);
        $this->settings ??= $service->group($this->getGroup());
        $state = $this->form->getState();

        foreach ($this->settings as $setting) {
            $newValue = $state[$setting->key] ?? null;
            $previousValue = $setting->value;

            // Sensitive fields send a placeholder when unchanged. Preserve the stored value.
            if ($setting->isSensitive() && $newValue === Setting::ENCRYPTED_PLACEHOLDER) {
                continue;
            }

            if ($setting->type === SettingType::IMAGE) {
                $newValue = $this->processImageSetting($setting, $newValue);
            }

            if ($setting->type === SettingType::JSON) {
                $newValue = $this->normaliseJson($newValue);
            }

            if ($setting->type === SettingType::BOOLEAN) {
                $newValue = $newValue ? '1' : '0';
            }

            $normalised = $newValue !== null ? (string) $newValue : null;

            if ($normalised !== $previousValue) {
                $setting->value = $normalised;
                $setting->save();

                AuditService::log(
                    auth()->user(),
                    'setting.updated',
                    $setting,
                    [
                        'key' => $setting->key,
                        'group' => $setting->group->value,
                        'previous_value' => $setting->isSensitive() ? '[redacted]' : $previousValue,
                        'new_value' => $setting->isSensitive() ? '[redacted]' : $normalised,
                    ]
                );
            }
        }

        $service->flushCache();

        Notification::make()
            ->title('Settings saved')
            ->body($this->getGroup()->label().' settings have been updated.')
            ->success()
            ->send();

        $this->redirect(SettingResource::getUrl("edit-{$this->getGroup()->value}"), navigate: true);
    }

    protected function processImageSetting(Setting $setting, mixed $state): ?string
    {
        if (! is_array($state) || count($state) === 0) {
            return null;
        }

        $first = $state[0];

        if ($first instanceof TemporaryUploadedFile) {
            $setting->clearMediaCollection('settings');
            $setting->addMedia($first->getRealPath())
                ->usingName($setting->key)
                ->usingFileName("{$setting->key}.".$first->getClientOriginalExtension())
                ->toMediaCollection('settings');

            return $setting->fresh()->value;
        }

        return $first;
    }

    protected function normaliseJson(mixed $value): ?string
    {
        if (is_string($value)) {
            json_decode($value);

            return json_last_error() === JSON_ERROR_NONE ? $value : null;
        }

        return $value !== null ? json_encode($value) : null;
    }
}
