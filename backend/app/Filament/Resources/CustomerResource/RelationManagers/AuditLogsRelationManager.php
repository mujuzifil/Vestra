<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\AuditLog;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $title = 'Activity';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'login') => 'success',
                        str_contains($state, 'deleted') => 'danger',
                        str_contains($state, 'updated') => 'warning',
                        str_contains($state, 'created') => 'info',
                        str_contains($state, 'password') => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('details')
                    ->getStateUsing(fn (AuditLog $record): string => $record->details ? json_encode($record->details) : '—')
                    ->limit(60)
                    ->tooltip(fn (AuditLog $record): ?string => $record->details ? json_encode($record->details) : null),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Browser')
                    ->limit(30)
                    ->tooltip(fn (AuditLog $record): ?string => $record->user_agent)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(fn (): array => AuditLog::distinct()->pluck('action')->sort()->mapWithKeys(fn ($action): array => [$action => $action])->toArray())
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (AuditLog $record): string => route('filament.admin.resources.audit-logs.view', $record)),
            ])
            ->bulkActions([]);
    }
}
