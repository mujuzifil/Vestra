<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminSessionResource\Pages;
use App\Models\AdminSession;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdminSessionResource extends Resource
{
    protected static ?string $model = AdminSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Sessions';

    protected static ?string $modelLabel = 'Session';

    protected static ?string $pluralModelLabel = 'Sessions';

    protected static ?int $navigationSort = 7;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user')
            ->latest('last_activity_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('session_id')->disabled(),
                Forms\Components\TextInput::make('ip_address')->disabled(),
                Forms\Components\DateTimePicker::make('last_activity_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->getStateUsing(fn (AdminSession $record): bool => $record->isCurrent()),

                Tables\Columns\TextColumn::make('device')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('os')
                    ->label('Operating system')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('browser')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('Last activity')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('current_user')
                    ->label('My sessions')
                    ->query(fn (Builder $query): Builder => $query->where('user_id', auth()->id())),
            ])
            ->actions([
                Tables\Actions\Action::make('terminate')
                    ->label('Terminate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (AdminSession $record): void {
                        if ($record->isCurrent()) {
                            session()->invalidate();
                        }

                        $record->delete();

                        AuditService::log(
                            auth()->user(),
                            'session.terminated',
                            $record,
                            ['user_id' => $record->user_id, 'session_id' => $record->session_id]
                        );

                        Notification::make()
                            ->title('Session terminated')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (AdminSession $record): bool => $record->isCurrent() && AdminSession::where('user_id', $record->user_id)->count() === 1),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('terminate')
                        ->label('Terminate selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->isCurrent()) {
                                    session()->invalidate();
                                }

                                $record->delete();

                                AuditService::log(
                                    auth()->user(),
                                    'session.terminated',
                                    $record,
                                    ['user_id' => $record->user_id, 'session_id' => $record->session_id]
                                );
                            }

                            Notification::make()
                                ->title('Sessions terminated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('last_activity_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminSessions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
