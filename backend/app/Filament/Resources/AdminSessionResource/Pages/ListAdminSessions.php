<?php

namespace App\Filament\Resources\AdminSessionResource\Pages;

use App\Filament\Resources\AdminSessionResource;
use App\Models\AdminSession;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAdminSessions extends ListRecords
{
    protected static string $resource = AdminSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('terminateAllOthers')
                ->label('Terminate all other sessions')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    AdminSession::where('user_id', auth()->id())
                        ->where('session_id', '!=', session()->getId())
                        ->delete();

                    AuditService::log(
                        auth()->user(),
                        'session.terminate_all_others',
                        auth()->user()
                    );

                    Notification::make()
                        ->title('Other sessions terminated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
