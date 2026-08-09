<?php

namespace App\Filament\Resources;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Filament\Resources\DistributorResource\Pages;
use App\Models\Distributor;
use App\Models\User;
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

    /**
     * Distributors → Active Partners is served by ActivePartnersPage.
     * Keep this resource for create/edit/view deep-link access.
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
                Forms\Components\Section::make('Company')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Linked account')
                            ->options(fn () => User::query()
                                ->whereDoesntHave('distributor')
                                ->orderBy('email')
                                ->pluck('email', 'id'))
                            ->searchable()
                            ->required()
                            ->visibleOn('create'),

                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('trading_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('primary_contact_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp number')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options(collect(DistributorAccountStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->default(DistributorAccountStatus::ACTIVE->value),

                        Forms\Components\Select::make('tier')
                            ->label('Distributor tier')
                            ->options(collect(DistributorTier::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                            ->required()
                            ->default(DistributorTier::SILVER->value),

                        Forms\Components\Select::make('stock_availability')
                            ->label('Stock availability')
                            ->options(collect(DistributorStockAvailability::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->default(DistributorStockAvailability::IN_STOCK->value),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location & hours')
                    ->schema([
                        Forms\Components\TextInput::make('district')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->label('Area / town')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('country')
                            ->maxLength(255)
                            ->default('Uganda'),

                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('google_maps_url')
                            ->label('Google Maps URL')
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('operating_hours_json')
                            ->label('Opening hours')
                            ->keyLabel('Day / period')
                            ->valueLabel('Hours')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('tier')
                    ->badge()
                    ->formatStateUsing(fn (?DistributorTier $state): string => $state?->label() ?? '—')
                    ->color(fn (?DistributorTier $state): string => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('district')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Area / town')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock_availability')
                    ->label('Stock')
                    ->badge()
                    ->formatStateUsing(fn (?DistributorStockAvailability $state): string => $state?->label() ?? '—')
                    ->color(fn (?DistributorStockAvailability $state): string => $state?->color() ?? 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('tier')
                    ->options(collect(DistributorTier::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\SelectFilter::make('stock_availability')
                    ->label('Stock')
                    ->options(collect(DistributorStockAvailability::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('editPartner')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Distributor $record): string => \App\Filament\Pages\Distributors\PartnerEditPage::getUrl(['partner' => $record->id])),
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
        return ['company_name', 'trading_name', 'email', 'phone', 'registration_number', 'district', 'city'];
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
            'Tier' => $record->tier?->label() ?? '—',
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
            'create' => Pages\CreateDistributor::route('/create'),
            'view' => Pages\ViewDistributor::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
