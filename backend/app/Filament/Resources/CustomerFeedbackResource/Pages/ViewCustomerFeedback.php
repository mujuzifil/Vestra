<?php

namespace App\Filament\Resources\CustomerFeedbackResource\Pages;

use App\Enums\FeedbackStatus;
use App\Filament\Resources\CustomerFeedbackResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerFeedback extends ViewRecord
{
    protected static string $resource = CustomerFeedbackResource::class;

    protected static string $view = 'filament.resources.customer-feedback-resource.pages.view-customer-feedback';

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
                    Notification::make()->title('Feedback marked as read')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if ($record->status !== FeedbackStatus::RESOLVED->value) {
            $actions[] = Actions\Action::make('markResolved')
                ->label('Mark Resolved')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => FeedbackStatus::RESOLVED->value]);
                    Notification::make()->title('Feedback marked resolved')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
