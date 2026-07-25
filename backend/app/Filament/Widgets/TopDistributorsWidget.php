<?php

namespace App\Filament\Widgets;

use App\Models\Distributor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopDistributorsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Distributor::query()
                    ->withSum('orders', 'total_amount')
                    ->withCount('orders')
                    ->orderByDesc('orders_sum_total_amount')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Distributor')
                    ->searchable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Total Purchases')
                    ->money('UGX')
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('creditAccount.balance')
                    ->label('Outstanding')
                    ->money('UGX')
                    ->alignment('right')
                    ->placeholder('UGX 0'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->heading('Top Distributors')
            ->paginated(false)
            ->striped();
    }
}
