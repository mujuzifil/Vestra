<?php

namespace App\Filament\Resources\QuotationRequestResource\Pages;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationRequestResource;
use App\Models\QuotationRequest;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotationRequest extends ViewRecord
{
    protected static string $resource = QuotationRequestResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions = [
            Actions\Action::make('edit')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];

        if (in_array($record->status, [QuotationStatus::SUBMITTED, QuotationStatus::DRAFT], true)) {
            $actions[] = Actions\Action::make('review')
                ->label('Review')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => QuotationStatus::REVIEWED]);
                    AuditService::log(auth()->user(), 'quotation.reviewed', $record, ['reference' => $record->reference_number]);
                    Notification::make()->title('Quotation marked as reviewed')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if (in_array($record->status, [QuotationStatus::REVIEWED, QuotationStatus::QUOTED, QuotationStatus::SUBMITTED], true)) {
            $actions[] = Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => QuotationStatus::ACCEPTED]);
                    AuditService::log(auth()->user(), 'quotation.approved', $record, ['reference' => $record->reference_number]);
                    Notification::make()->title('Quotation approved')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if (! in_array($record->status, [QuotationStatus::REJECTED, QuotationStatus::CONVERTED_TO_ORDER], true)) {
            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => QuotationStatus::REJECTED]);
                    AuditService::log(auth()->user(), 'quotation.rejected', $record, ['reference' => $record->reference_number]);
                    Notification::make()->title('Quotation rejected')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        if (in_array($record->status, [QuotationStatus::ACCEPTED, QuotationStatus::QUOTED], true)) {
            $actions[] = Actions\Action::make('convertToOrder')
                ->label('Convert to Order')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Convert Quotation to Order')
                ->modalDescription('This will convert the quotation into an order. Full conversion logic will be implemented in a future release.')
                ->action(function () use ($record): void {
                    $record->update(['status' => QuotationStatus::CONVERTED_TO_ORDER]);
                    AuditService::log(auth()->user(), 'quotation.converted_to_order', $record, ['reference' => $record->reference_number]);
                    Notification::make()->title('Quotation converted to order (placeholder)')->success()->send();
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
