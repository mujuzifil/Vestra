<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Event')
                    ->schema([
                        TextEntry::make('action')
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                str_contains($state, 'login') => 'success',
                                str_contains($state, 'deleted') => 'danger',
                                str_contains($state, 'updated') => 'warning',
                                str_contains($state, 'created') => 'info',
                                str_contains($state, 'password') => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('System / Guest'),

                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Subject')
                    ->schema([
                        TextEntry::make('subject_type')
                            ->label('Entity type')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'None'),

                        TextEntry::make('subject_id')
                            ->label('Entity ID')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),

                Section::make('Changes')
                    ->schema([
                        TextEntry::make('details')
                            ->label('Details')
                            ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_PRETTY_PRINT) : '{}')
                            ->extraAttributes(['class' => 'font-mono text-sm'])
                            ->columnSpanFull(),
                    ]),

                Section::make('Request')
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('IP address'),

                        TextEntry::make('user_agent')
                            ->label('User agent')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),
            ]);
    }
}
