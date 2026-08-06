<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorBranchResource\Pages;
use App\Models\DistributorBranch;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorBranchResource extends Resource
{
    protected static ?string $model = DistributorBranch::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Territories';

    protected static ?string $label = 'Branch';

    protected static ?int $navigationSort = 3;

    /**
     * Distributors → Territories is served by TerritoriesPage.
     * Keep this resource only for branch record create/edit deep links.
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationItems(): array
    {
        return [];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branch Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\Select::make('distributor_id')
                            ->label('Distributor')
                            ->relationship('distributor', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Kampala Branch'),

                        Forms\Components\TextInput::make('manager_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Branch'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('district')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->placeholder('e.g. 0.3476'),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->placeholder('e.g. 32.5825'),

                        Forms\Components\Textarea::make('delivery_notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('distributor.company_name')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold'),

                Tables\Columns\TextColumn::make('manager_name')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('city')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'company_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DistributorBranch $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_branch.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorBranch $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_branch.deleted',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                AuditService::log(
                                    auth()->user(),
                                    'distributor_branch.deleted',
                                    $record,
                                    ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                                );
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('distributor');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributorBranches::route('/'),
            'edit' => Pages\EditDistributorBranch::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
