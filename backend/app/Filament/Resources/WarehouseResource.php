<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Warehouse Details')
                    ->icon('heroicon-o-building-office-2')
                    ->description('Physical storage location details.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Kampala Central Warehouse'),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. KLA-01'),

                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Full warehouse address')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Manager Contact')
                    ->icon('heroicon-o-user')
                    ->description('On-site warehouse manager details.')
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->label('Manager Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_phone')
                            ->label('Manager Phone')
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('manager_email')
                            ->label('Manager Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('manager_name')
                    ->label('Manager')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manager_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable()
                    ->alignment('center')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stocks_count')
                    ->label('Stock Lines')
                    ->badge()
                    ->color('gray')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->after(function (Warehouse $record) {
                            AuditService::log(
                                auth()->user(),
                                'warehouse.updated',
                                $record,
                                ['name' => $record->name, 'code' => $record->code, 'is_active' => $record->is_active]
                            );
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->before(function (Warehouse $record) {
                            AuditService::log(
                                auth()->user(),
                                'warehouse.deleted',
                                $record,
                                ['name' => $record->name, 'code' => $record->code]
                            );
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('stocks');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
