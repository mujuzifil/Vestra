<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\PaymentTransaction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentTransactions';

    protected static ?string $title = 'Payments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_reference')
            ->columns([
                Tables\Columns\TextColumn::make('order.invoice_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => \App\Enums\PaymentMethod::tryFrom($state)?->label() ?? ucfirst($state)),

                Tables\Columns\TextColumn::make('provider')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('transaction_reference')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending'),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn (): array => PaymentTransaction::distinct()->pluck('status')->filter()->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->toArray())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(collect(\App\Enums\PaymentMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()]))
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (PaymentTransaction $record): string => route('filament.admin.resources.orders.view', $record->order_id)),
            ])
            ->bulkActions([]);
    }
}
