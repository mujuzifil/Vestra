<?php

namespace App\Filament\Resources\DistributorRequestResource\Pages;

use App\Enums\DistributorStatus;
use App\Filament\Resources\DistributorRequestResource;
use App\Services\DistributorOnboardingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDistributorRequest extends ViewRecord
{
    protected static string $resource = DistributorRequestResource::class;

    protected static string $view = 'filament.resources.distributor-request-resource.pages.view-distributor-request';

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $service = app(DistributorOnboardingService::class);

        $actions = [
            Actions\Action::make('edit')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];

        if ($record->status !== DistributorStatus::APPROVED || ! $record->distributor()->exists()) {
            $actions[] = Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $record->status !== DistributorStatus::REJECTED)
                ->action(function () use ($record, $service): void {
                    $service->approve($record, auth()->user());
                    Notification::make()->title('Application approved and distributor account created.')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if ($record->status !== DistributorStatus::REJECTED) {
            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record, $service): void {
                    $service->reject($record, null, auth()->user());
                    Notification::make()->title('Application rejected')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if (! in_array($record->status->value, [DistributorStatus::APPROVED->value, DistributorStatus::REJECTED->value, DistributorStatus::INFORMATION_REQUESTED->value], true)) {
            $actions[] = Actions\Action::make('requestInformation')
                ->label('Request Info')
                ->icon('heroicon-o-question-mark-circle')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () use ($record, $service): void {
                    $service->requestInformation($record, null, auth()->user());
                    Notification::make()->title('Information requested')->info()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if (in_array($record->status->value, [DistributorStatus::PENDING->value, DistributorStatus::INFORMATION_REQUESTED->value], true)) {
            $actions[] = Actions\Action::make('returnToReview')
                ->label('Return to Review')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () use ($record, $service): void {
                    $service->markUnderReview($record, auth()->user());
                    Notification::make()->title('Application returned to review')->warning()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
