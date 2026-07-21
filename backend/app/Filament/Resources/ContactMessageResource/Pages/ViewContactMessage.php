<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Enums\ContactStatus;
use App\Filament\Resources\ContactMessageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected static string $view = 'filament.resources.contact-message-resource.pages.view-contact-message';

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions = [
            Actions\Action::make('edit')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];

        if (! $record->isRead()) {
            $actions[] = Actions\Action::make('markRead')
                ->label('Mark Read')
                ->icon('heroicon-o-envelope-open')
                ->color('success')
                ->action(function () use ($record): void {
                    $record->markAsRead();
                    Notification::make()->title('Message marked as read')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if ($record->status !== ContactStatus::RESOLVED->value) {
            $actions[] = Actions\Action::make('markResolved')
                ->label('Mark Resolved')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => ContactStatus::RESOLVED->value]);
                    Notification::make()->title('Message marked resolved')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
