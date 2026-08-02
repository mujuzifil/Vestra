<?php

namespace App\Filament\Resources;

use App\Enums\NotificationChannel;
use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?string $navigationLabel = 'Templates';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Details')
                    ->schema([
                        Forms\Components\TextInput::make('event_key')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. order.created'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('category')
                            ->maxLength(100),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'critical' => 'Critical',
                            ])
                            ->default('normal'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Channels')
                    ->schema([
                        Forms\Components\CheckboxList::make('channels_json')
                            ->label('Enabled Channels')
                            ->options(NotificationChannel::class)
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('email_body')
                            ->rows(6)
                            ->columnSpanFull()
                            ->extraInputAttributes(['class' => 'font-mono'])
                            ->helperText('Supports HTML and {{variable}} placeholders.'),

                        Forms\Components\Textarea::make('sms_body')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Keep under 160 characters where possible. Supports {{variable}} placeholders.'),

                        Forms\Components\Textarea::make('in_app_body')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Supports {{variable}} placeholders.'),
                    ]),

                Forms\Components\Section::make('Variables')
                    ->schema([
                        Forms\Components\KeyValue::make('variables_json')
                            ->label('Available Variables')
                            ->keyLabel('Variable')
                            ->valueLabel('Description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_key')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('font-mono'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'customer' => 'Customer',
                        'order' => 'Order',
                        'distributor' => 'Distributor',
                        'security' => 'Security',
                        'announcement' => 'Announcement',
                        'system' => 'System',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_key');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
