<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorProductPriceResource\Pages;
use App\Models\DistributorProductPrice;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorProductPriceResource extends Resource
{
    protected static ?string $model = DistributorProductPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Product Prices';

    protected static ?string $label = 'Product Price';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Price Assignment')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\Select::make('distributor_id')
                            ->label('Distributor')
                            ->relationship('distributor', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('price')
                            ->label('Negotiated Price')
                            ->required()
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->native(false),

                        Forms\Components\DatePicker::make('effective_until')
                            ->label('Effective Until')
                            ->native(false)
                            ->afterOrEqual('effective_from'),
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

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('effective_from')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('effective_until')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('isCurrentlyEffective')
                    ->label('Effective')
                    ->boolean()
                    ->state(fn (DistributorProductPrice $record): bool => $record->isCurrentlyEffective()),

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

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('currently_effective')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $q) {
                            $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', now());
                        })
                        ->where(function (Builder $q) {
                            $q->whereNull('effective_until')->orWhereDate('effective_until', '>=', now());
                        }))
                    ->toggle(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DistributorProductPrice $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_product_price.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id, 'price' => $record->price]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorProductPrice $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_product_price.deleted',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id]
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
                                    'distributor_product_price.deleted',
                                    $record,
                                    ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id]
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
            ->with(['distributor', 'product']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributorProductPrices::route('/'),
            'create' => Pages\CreateDistributorProductPrice::route('/create'),
            'edit' => Pages\EditDistributorProductPrice::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
