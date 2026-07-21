<?php

namespace App\Filament\Resources;

use App\Enums\SettingGroup;
use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'image' => 'Image',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'json' => 'JSON',
                        'select' => 'Select',
                    ])
                    ->disabled(),

                Forms\Components\TextInput::make('value')
                    ->maxLength(65535)
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => $record?->isSensitive() || ($get('type') !== 'string' && $get('type') !== 'number'))
                    ->numeric(fn (Forms\Get $get) => $get('type') === 'number')
                    ->autocomplete(false),

                Forms\Components\Textarea::make('value')
                    ->rows(4)
                    ->columnSpanFull()
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => $record?->isSensitive() || ($get('type') !== 'text' && $get('type') !== 'json'))
                    ->extraInputAttributes(['class' => 'font-mono'])
                    ->hint(fn (Forms\Get $get) => $get('type') === 'json' ? 'Valid JSON required' : null),

                Forms\Components\Toggle::make('value')
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => $record?->isSensitive() || $get('type') !== 'boolean')
                    ->dehydrateStateUsing(fn (?bool $state): string => $state ? '1' : '0')
                    ->formatStateUsing(fn (?string $state): bool => $state === '1'),

                Forms\Components\Select::make('value')
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => $record?->isSensitive() || $get('type') !== 'select')
                    ->options(fn (Forms\Get $get, ?Setting $record) => $record?->selectOptions() ?? []),

                Forms\Components\TextInput::make('value')
                    ->label(fn (?Setting $record) => $record?->label)
                    ->helperText(fn (?Setting $record) => $record?->description)
                    ->password()
                    ->revealable()
                    ->autocomplete(false)
                    ->placeholder('Enter new value to replace the stored secret')
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => ! $record?->isSensitive())
                    ->formatStateUsing(fn (?string $state, ?Setting $record): string => $record?->isSensitive() && filled($state) ? Setting::ENCRYPTED_PLACEHOLDER : $state),

                Forms\Components\SpatieMediaLibraryFileUpload::make('settings_image')
                    ->collection('settings')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                    ->hidden(fn (Forms\Get $get, ?Setting $record): bool => $record?->isSensitive() || $get('type') !== 'image')
                    ->columnSpanFull()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('font-mono')
                    ->color('text-neutral-500'),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->sortable()
                    ->color(fn (Setting $record): string => match ($record->group) {
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
                        default => 'gray',
                    }),

                Tables\Columns\ImageColumn::make('value')
                    ->hidden(fn (?Setting $record): bool => $record?->type->value !== 'image')
                    ->circular(),

                Tables\Columns\IconColumn::make('value')
                    ->hidden(fn (?Setting $record): bool => $record?->type->value !== 'boolean')
                    ->boolean(),

                Tables\Columns\TextColumn::make('value')
                    ->hidden(fn (?Setting $record): bool => $record?->type->value === 'image' || $record?->type->value === 'boolean')
                    ->formatStateUsing(fn (?string $state, Setting $record): string => $record->isSensitive() ? '••••••••' : (string) $state)
                    ->limit(50)
                    ->tooltip(fn (Setting $record): ?string => ($record->type->value === 'text' || $record->type->value === 'json') && ! $record->isSensitive() ? $record->value : null),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(SettingGroup::class)
                    ->multiple()
                    ->native(false),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'image' => 'Image',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'json' => 'JSON',
                        'select' => 'Select',
                    ])
                    ->multiple()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Setting $record): array {
                        if ($record->isSensitive() && filled($record->value)) {
                            $data['value'] = Setting::ENCRYPTED_PLACEHOLDER;
                        } elseif ($record->type->value === 'boolean') {
                            $data['value'] = $record->typedValue();
                        } elseif ($record->type->value === 'select') {
                            $data['value'] = $record->value;
                        }

                        return $data;
                    })
                    ->using(function (Setting $record, array $data): Setting {
                        $previousValue = $record->value;

                        if ($record->isSensitive()) {
                            if (array_key_exists('value', $data) && $data['value'] !== Setting::ENCRYPTED_PLACEHOLDER) {
                                $record->value = $data['value'];
                            }
                        } elseif ($record->type->value === 'boolean') {
                            $record->value = $data['value'] ? '1' : '0';
                        } elseif ($record->type->value === 'image') {
                            // Image is handled by Spatie media upload; value unchanged unless media replaced.
                        } else {
                            $record->value = $data['value'];
                        }

                        $record->save();

                        $auditValue = $record->isSensitive() ? '[redacted]' : $record->value;

                        AuditService::log(
                            auth()->user(),
                            'setting.updated',
                            $record,
                            [
                                'key' => $record->key,
                                'group' => $record->group->value,
                                'previous_value' => $record->isSensitive() ? '[redacted]' : $previousValue,
                                'new_value' => $auditValue,
                            ]
                        );

                        return $record;
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('group')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit-general' => Pages\EditGeneralSettings::route('/general/edit'),
            'edit-business' => Pages\EditBusinessSettings::route('/business/edit'),
            'edit-commerce' => Pages\EditCommerceSettings::route('/commerce/edit'),
            'edit-orders' => Pages\EditOrderSettings::route('/orders/edit'),
            'edit-payments' => Pages\EditPaymentSettings::route('/payments/edit'),
            'edit-inventory' => Pages\EditInventorySettings::route('/inventory/edit'),
            'edit-notifications' => Pages\EditNotificationSettings::route('/notifications/edit'),
            'edit-email' => Pages\EditEmailSettings::route('/email/edit'),
            'edit-localization' => Pages\EditLocalizationSettings::route('/localization/edit'),
            'edit-security' => Pages\EditSecuritySettings::route('/security/edit'),
            'edit-integrations' => Pages\EditIntegrationSettings::route('/integrations/edit'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
