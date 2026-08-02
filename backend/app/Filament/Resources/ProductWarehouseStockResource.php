<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductWarehouseStockResource\Pages;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\Warehouse;
use App\Services\AuditService;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouseStockResource extends Resource
{
    protected static ?string $model = ProductWarehouseStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Products';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse.code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->sortable()
                    ->alignment('right')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Available')
                    ->state(fn (ProductWarehouseStock $record): int => $record->availableQuantity())
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->sortable()
                    ->alignment('right')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_low_stock')
                    ->label('Low Stock')
                    ->state(fn (ProductWarehouseStock $record): bool => $record->isLowStock())
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->searchable()
                    ->options(fn (): array => Warehouse::query()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->searchable()
                    ->options(fn (): array => Product::query()->pluck('name', 'id')->toArray()),

                Tables\Filters\TernaryFilter::make('low_stock')
                    ->label('Low Stock')
                    ->placeholder('Any stock level')
                    ->trueLabel('Low stock only')
                    ->falseLabel('Normal stock')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereRaw('(quantity - reserved_quantity) <= reorder_level'),
                        false: fn (Builder $query): Builder => $query->whereRaw('(quantity - reserved_quantity) > reorder_level'),
                    ),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->modalHeading('Manual Stock Adjustment')
                    ->modalDescription('Use positive values to increase stock and negative values to decrease stock.')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Adjustment Quantity')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->helperText('Positive adds stock, negative removes stock.'),

                        Forms\Components\TextInput::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Cycle count correction'),
                    ])
                    ->action(function (ProductWarehouseStock $record, array $data): void {
                        /** @var InventoryService $service */
                        $service = app(InventoryService::class);

                        $movement = $service->adjustStock(
                            $record->product,
                            $record->warehouse,
                            (int) $data['quantity'],
                            $data['reason'],
                            auth()->user()
                        );

                        AuditService::log(
                            auth()->user(),
                            'stock.adjusted',
                            $record,
                            [
                                'product_id' => $record->product_id,
                                'warehouse_id' => $record->warehouse_id,
                                'adjustment' => $data['quantity'],
                                'balance_after' => $movement->balance_after,
                                'reason' => $data['reason'],
                            ]
                        );

                        Notification::make()
                            ->title('Stock adjusted')
                            ->body("New balance for {$record->warehouse->name}: {$movement->balance_after}")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->defaultSort('updated_at', 'desc')
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
            'index' => Pages\ListProductWarehouseStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'warehouse']);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
