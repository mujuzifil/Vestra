<?php

namespace App\Filament\Resources\DistributorResource\RelationManagers;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationRequestResource;
use App\Models\QuotationRequest;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    protected static ?string $title = 'Quotations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state instanceof QuotationStatus ? $state->label() : ucfirst($state))
                    ->color(fn ($state): string => $state instanceof QuotationStatus ? $state->color() : 'gray'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quoted_at')
                    ->label('Quoted')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(QuotationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (QuotationRequest $record): string => QuotationRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
