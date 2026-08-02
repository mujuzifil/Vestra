<?php

namespace App\Filament\Resources\NotificationDeliveryResource\Pages;

use App\Filament\Resources\NotificationDeliveryResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificationDelivery extends ViewRecord
{
    protected static string $resource = NotificationDeliveryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Delivery Details')
                    ->icon('heroicon-o-paper-airplane')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('channel')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('recipient')
                            ->placeholder('No recipient'),

                        TextEntry::make('subject')
                            ->placeholder('No subject'),

                        TextEntry::make('user.name')
                            ->label('Recipient User')
                            ->placeholder('Guest / system'),

                        TextEntry::make('template.event_key')
                            ->label('Template')
                            ->placeholder('Fallback'),

                        TextEntry::make('sent_at')
                            ->dateTime()
                            ->placeholder('Not sent yet'),

                        TextEntry::make('opened_at')
                            ->dateTime()
                            ->placeholder('Not opened yet'),
                    ]),

                Section::make('Content')
                    ->schema([
                        TextEntry::make('content')
                            ->html()
                            ->placeholder('No content')
                            ->columnSpanFull(),
                    ]),

                Section::make('Variables')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('variables_json')
                            ->label('Rendered Variables')
                            ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_PRETTY_PRINT) : '{}')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),

                Section::make('Error Information')
                    ->visible(fn ($record): bool => filled($record->error_message))
                    ->schema([
                        TextEntry::make('error_message')
                            ->color('danger')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
