<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorPriceTierResource\Pages;
use App\Models\DistributorPriceTier;
use App\Models\Product;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorPriceTierResource extends Resource
{
    protected static ?string $model = DistributorPriceTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Price Tiers';

    protected static ?string $label = 'Price Tier';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tier Configuration')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('min_quantity')
                            ->label('Minimum Quantity')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_quantity')
                            ->label('Maximum Quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->nullable()
                            ->helperText('Leave empty for open-ended tiers.'),

                        Forms\Components\TextInput::make('price')
                            ->label('Tier Price')
                            ->required()
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Min Qty')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('max_quantity')
                    ->label('Max Qty')
                    ->numeric()
                    ->sortable()
                    ->alignment('center')
                    ->placeholder('∞'),

                Tables\Columns\TextColumn::make('price')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DistributorPriceTier $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_price_tier.updated',
                            $record,
                            ['product_id' => $record->product_id, 'price' => $record->price]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorPriceTier $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_price_tier.deleted',
                            $record,
                            ['product_id' => $record->product_id, 'price' => $record->price]
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
                                    'distributor_price_tier.deleted',
                                    $record,
                                    ['product_id' => $record->product_id, 'price' => $record->price]
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
            ->with('product');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributorPriceTiers::route('/'),
            'create' => Pages\CreateDistributorPriceTier::route('/create'),
            'edit' => Pages\EditDistributorPriceTier::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
