<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->lowStock()
                    ->with(['category'])
                    ->orderBy('stock_quantity')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('UGX')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record))
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton(),
            ])
            ->heading('Low Stock Products')
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('View all products')
                    ->url(route('filament.admin.resources.products.index'))
                    ->icon('heroicon-m-arrow-right')
                    ->link(),
            ])
            ->paginated(false);
    }
}
