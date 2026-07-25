<?php

namespace App\Filament\Resources;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Filament\Resources\NotificationDeliveryResource\Pages;
use App\Models\NotificationDelivery;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotificationDeliveryResource extends Resource
{
    protected static ?string $model = NotificationDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 81;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('channel')
                    ->badge()
                    ->color(fn (NotificationChannel $state): string => match ($state) {
                        NotificationChannel::EMAIL => 'info',
                        NotificationChannel::SMS => 'warning',
                        NotificationChannel::IN_APP => 'success',
                        NotificationChannel::PUSH => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('recipient')
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (NotificationStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('template.event_key')
                    ->label('Template')
                    ->placeholder('Fallback'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->options(NotificationChannel::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(NotificationStatus::class)
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationDeliveries::route('/'),
        ];
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

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
