<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected static string $view = 'filament.resources.review-resource.pages.view-review';

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions = [
            Actions\Action::make('edit')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];

        if ($record->status === ReviewStatus::PENDING->value) {
            $actions[] = Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => ReviewStatus::APPROVED->value]);
                    Notification::make()->title('Review approved')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });

            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => ReviewStatus::REJECTED->value]);
                    Notification::make()->title('Review rejected')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if ($record->is_hidden) {
            $actions[] = Actions\Action::make('restore')
                ->label('Restore')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['is_hidden' => false]);
                    Notification::make()->title('Review restored')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        } else {
            $actions[] = Actions\Action::make('hide')
                ->label('Hide')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['is_hidden' => true]);
                    Notification::make()->title('Review hidden')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
