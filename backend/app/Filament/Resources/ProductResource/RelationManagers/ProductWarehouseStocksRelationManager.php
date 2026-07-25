<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductWarehouseStock;
use App\Models\Warehouse;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductWarehouseStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouseStocks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold'),

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
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
