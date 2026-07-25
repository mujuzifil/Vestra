<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->badge()
                    ->color('gray')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->formatStateUsing(fn (string $state): string => PaymentStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => PaymentStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => OrderStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => OrderStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->multiple()
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->bulkActions([]);
    }
}
