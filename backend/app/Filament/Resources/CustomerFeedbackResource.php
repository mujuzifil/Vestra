<?php

namespace App\Filament\Resources;

use App\Enums\FeedbackCategory;
use App\Enums\FeedbackStatus;
use App\Enums\Priority;
use App\Filament\Resources\CustomerFeedbackResource\Pages;
use App\Models\CustomerFeedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerFeedbackResource extends Resource
{
    protected static ?string $model = CustomerFeedback::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Requests';
    protected static ?string $navigationLabel = 'Customer Feedbacks';
    protected static ?string $label = 'Customer Feedback';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Feedback Details')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled(),
                        Forms\Components\TextInput::make('category')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Handling')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                FeedbackStatus::NEW->value => FeedbackStatus::NEW->label(),
                                FeedbackStatus::IN_PROGRESS->value => FeedbackStatus::IN_PROGRESS->label(),
                                FeedbackStatus::RESOLVED->value => FeedbackStatus::RESOLVED->label(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('customer')
                    ->label('Customer')
                    ->view('filament.tables.columns.feedback-customer')
                    ->alignment('left'),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
                    ->weight('font-semibold'),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn (string $state): string => FeedbackCategory::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('priority')
                    ->formatStateUsing(fn (string $state): string => Priority::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn (string $state): string => Priority::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => FeedbackStatus::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn (string $state): string => FeedbackStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Subject, message, customer...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('subject', 'like', "%{$term}%")
                              ->orWhere('message', 'like', "%{$term}%")
                              ->orWhereHas('user', fn (Builder $uq) => $uq->where('name', 'like', "%{$term}%")
                                  ->orWhere('email', 'like', "%{$term}%"));
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        FeedbackStatus::NEW->value => FeedbackStatus::NEW->label(),
                        FeedbackStatus::IN_PROGRESS->value => FeedbackStatus::IN_PROGRESS->label(),
                        FeedbackStatus::RESOLVED->value => FeedbackStatus::RESOLVED->label(),
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        FeedbackCategory::GENERAL->value => FeedbackCategory::GENERAL->label(),
                        FeedbackCategory::BUG->value => FeedbackCategory::BUG->label(),
                        FeedbackCategory::FEATURE->value => FeedbackCategory::FEATURE->label(),
                        FeedbackCategory::COMPLAINT->value => FeedbackCategory::COMPLAINT->label(),
                        FeedbackCategory::PRAISE->value => FeedbackCategory::PRAISE->label(),
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

                Tables\Filters\Filter::make('recently_received')
                    ->label('Recently Submitted')
                    ->query(fn (Builder $query): Builder => $query->recentlyReceived(7))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_updated')
                    ->label('Recently Updated')
                    ->query(fn (Builder $query): Builder => $query->recentlyUpdated(7))
                    ->toggle(),

                Tables\Filters\Filter::make('submitted_at')
                    ->label('Submitted Date')
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
                Tables\Actions\Action::make('markInProgress')
                    ->label('Mark In Progress')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (CustomerFeedback $record): bool => $record->status !== FeedbackStatus::RESOLVED->value)
                    ->action(fn (CustomerFeedback $record) => $record->update(['status' => FeedbackStatus::IN_PROGRESS->value])),
                Tables\Actions\Action::make('markResolved')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CustomerFeedback $record): bool => $record->status !== FeedbackStatus::RESOLVED->value)
                    ->action(fn (CustomerFeedback $record) => $record->update(['status' => FeedbackStatus::RESOLVED->value])),
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
                            Notification::make()->title('Feedback marked as read')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markUnread')
                        ->label('Mark Unread')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->markAsUnread();
                            Notification::make()->title('Feedback marked as unread')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markInProgress')
                        ->label('Mark In Progress')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => FeedbackStatus::IN_PROGRESS->value]);
                            Notification::make()->title('Feedback marked in progress')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markResolved')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => FeedbackStatus::RESOLVED->value]);
                            Notification::make()->title('Feedback marked resolved')->success()->send();
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
                        ->modalHeading('Archive Feedback')
                        ->modalDescription('Archiving will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Archiving integration is planned')->info()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (CustomerFeedback $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No feedback found')
            ->emptyStateDescription('Customer feedback will appear here once it is submitted.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerFeedback::route('/'),
            'view' => Pages\ViewCustomerFeedback::route('/{record}'),
            'edit' => Pages\EditCustomerFeedback::route('/{record}/edit'),
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
