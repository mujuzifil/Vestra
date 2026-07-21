<?php

namespace App\Filament\Resources;

use App\Enums\ContactStatus;
use App\Enums\Priority;
use App\Filament\Resources\ContactMessageResource\Pages;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Requests';
    protected static ?string $navigationLabel = 'Contact Messages';
    protected static ?string $label = 'Contact Message';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Message')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Sender')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Handling')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                ContactStatus::NEW->value => ContactStatus::NEW->label(),
                                ContactStatus::IN_PROGRESS->value => ContactStatus::IN_PROGRESS->label(),
                                ContactStatus::RESOLVED->value => ContactStatus::RESOLVED->label(),
                            ])
                            ->native(false),
                        Forms\Components\Select::make('priority')
                            ->required()
                            ->options([
                                Priority::CRITICAL->value => Priority::CRITICAL->label(),
                                Priority::HIGH->value => Priority::HIGH->label(),
                                Priority::MEDIUM->value => Priority::MEDIUM->label(),
                                Priority::LOW->value => Priority::LOW->label(),
                                Priority::NEUTRAL->value => Priority::NEUTRAL->label(),
                            ])
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Admin Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        Forms\Components\Textarea::make('reply')
                            ->label('Your Reply')
                            ->rows(5)
                            ->columnSpanFull()
                            ->placeholder('Type your reply here...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('sender')
                    ->label('Sender')
                    ->view('filament.tables.columns.contact-sender')
                    ->alignment('left'),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
                    ->weight('font-semibold'),

                Tables\Columns\BadgeColumn::make('priority')
                    ->formatStateUsing(fn ($state): string => Priority::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn ($state): string => Priority::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state instanceof ContactStatus ? $state->label() : (ContactStatus::tryFrom($state)?->label() ?? ucfirst($state)))
                    ->color(fn ($state): string => $state instanceof ContactStatus ? $state->color() : (ContactStatus::tryFrom($state)?->color() ?? 'gray')),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('replied_at')
                    ->label('Replied')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-small')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Name, email, subject, message...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('name', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%")
                              ->orWhere('subject', 'like', "%{$term}%")
                              ->orWhere('message', 'like', "%{$term}%");
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ContactStatus::NEW->value => ContactStatus::NEW->label(),
                        ContactStatus::IN_PROGRESS->value => ContactStatus::IN_PROGRESS->label(),
                        ContactStatus::RESOLVED->value => ContactStatus::RESOLVED->label(),
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        Priority::CRITICAL->value => Priority::CRITICAL->label(),
                        Priority::HIGH->value => Priority::HIGH->label(),
                        Priority::MEDIUM->value => Priority::MEDIUM->label(),
                        Priority::LOW->value => Priority::LOW->label(),
                        Priority::NEUTRAL->value => Priority::NEUTRAL->label(),
                    ]),

                Tables\Filters\Filter::make('unread')
                    ->label('Unread')
                    ->query(fn (Builder $query): Builder => $query->unread())
                    ->toggle(),

                Tables\Filters\Filter::make('replied')
                    ->label('Replied')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('replied_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_received')
                    ->label('Recently Received')
                    ->query(fn (Builder $query): Builder => $query->recentlyReceived(7))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_updated')
                    ->label('Recently Updated')
                    ->query(fn (Builder $query): Builder => $query->recentlyUpdated(7))
                    ->toggle(),

                Tables\Filters\Filter::make('received_at')
                    ->label('Received Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'] ?? null, fn (Builder $q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('sendReply')
                    ->label('Send Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Reply')
                    ->modalDescription('This will send the reply email to the customer.')
                    ->visible(fn (ContactMessage $record): bool => filled($record->reply) && ! $record->replied_at)
                    ->action(function (ContactMessage $record) {
                        Mail::to($record->email)->send(new ContactReplyMail($record));
                        $record->update(['replied_at' => now(), 'status' => ContactStatus::RESOLVED->value]);
                        Notification::make()->title('Reply sent successfully')->success()->send();
                    }),
                Tables\Actions\Action::make('markResolved')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ContactMessage $record): bool => $record->status !== ContactStatus::RESOLVED->value)
                    ->action(fn (ContactMessage $record) => $record->update(['status' => ContactStatus::RESOLVED->value])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markRead')
                        ->label('Mark Read')
                        ->icon('heroicon-o-envelope-open')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->markAsRead();
                            Notification::make()->title('Messages marked as read')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markUnread')
                        ->label('Mark Unread')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->markAsUnread();
                            Notification::make()->title('Messages marked as unread')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markInProgress')
                        ->label('Mark In Progress')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => ContactStatus::IN_PROGRESS->value]);
                            Notification::make()->title('Messages marked in progress')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markResolved')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => ContactStatus::RESOLVED->value]);
                            Notification::make()->title('Messages marked resolved')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('assign')
                        ->label('Assign Administrator')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Assign Administrator')
                        ->modalDescription('Administrator assignment will be available when user management integration is completed. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Assignment integration is planned')->info()->send();
                        }),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Archive Messages')
                        ->modalDescription('Archiving will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Archiving integration is planned')->info()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ContactMessage $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No messages found')
            ->emptyStateDescription('Contact messages will appear here once customers reach out.')
            ->emptyStateIcon('heroicon-o-envelope');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
