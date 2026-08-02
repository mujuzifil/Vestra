<?php

namespace App\Filament\Resources;

use App\Enums\DistributorAccountStatus;
use App\Filament\Resources\DistributorResource\Pages;
use App\Models\Distributor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorResource extends Resource
{
    protected static ?string $model = Distributor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Active Partners';

    protected static ?string $modelLabel = 'Distributor';

    protected static ?string $pluralModelLabel = 'Distributors';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('trading_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options(collect(DistributorAccountStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('trading_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (DistributorAccountStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(DistributorAccountStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['company_name', 'trading_name', 'email', 'phone', 'registration_number'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->company_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email ?? '—',
            'Status' => $record->status->label(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\DistributorResource\RelationManagers\CreditAccountRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\BranchesRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\ContactsRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\QuotationsRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\OrdersRelationManager::class,
            \App\Filament\Resources\DistributorResource\RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributors::route('/'),
            'view' => Pages\ViewDistributor::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
