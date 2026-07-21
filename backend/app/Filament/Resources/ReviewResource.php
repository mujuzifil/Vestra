<?php

namespace App\Filament\Resources;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'E-Commerce';
    protected static ?string $navigationLabel = 'Reviews';
    protected static ?string $label = 'Review';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Review Details')
                    ->icon('heroicon-o-star')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled(),
                        Forms\Components\TextInput::make('product.name')
                            ->label('Product')
                            ->disabled(),
                        Forms\Components\TextInput::make('rating')
                            ->disabled()
                            ->suffix('/ 5'),
                        Forms\Components\TextInput::make('title')
                            ->disabled(),
                        Forms\Components\Textarea::make('comment')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Moderation')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                ReviewStatus::PENDING->value => ReviewStatus::PENDING->label(),
                                ReviewStatus::APPROVED->value => ReviewStatus::APPROVED->label(),
                                ReviewStatus::REJECTED->value => ReviewStatus::REJECTED->label(),
                            ])
                            ->native(false),
                        Forms\Components\Toggle::make('is_hidden')
                            ->label('Hidden')
                            ->helperText('Hidden reviews remain in the system but are not displayed publicly.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('product')
                    ->label('Product')
                    ->view('filament.tables.columns.review-product')
                    ->alignment('left'),

                Tables\Columns\ViewColumn::make('reviewer')
                    ->label('Reviewer')
                    ->view('filament.tables.columns.review-reviewer')
                    ->alignment('left'),

                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->color(fn (Review $record): string => $record->ratingColor())
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(35)
                    ->weight('font-semibold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ReviewStatus::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn (string $state): string => ReviewStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\BadgeColumn::make('is_hidden')
                    ->label('Visibility')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Hidden' : 'Visible')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),

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
                            ->placeholder('Product, customer, title, comment...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('title', 'like', "%{$term}%")
                              ->orWhere('comment', 'like', "%{$term}%")
                              ->orWhereHas('user', fn (Builder $uq) => $uq->where('name', 'like', "%{$term}%")
                                  ->orWhere('email', 'like', "%{$term}%"))
                              ->orWhereHas('product', fn (Builder $pq) => $pq->where('name', 'like', "%{$term}%"));
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ReviewStatus::PENDING->value => ReviewStatus::PENDING->label(),
                        ReviewStatus::APPROVED->value => ReviewStatus::APPROVED->label(),
                        ReviewStatus::REJECTED->value => ReviewStatus::REJECTED->label(),
                    ]),

                Tables\Filters\SelectFilter::make('rating')
                    ->options([1 => '1 Star', 2 => '2 Stars', 3 => '3 Stars', 4 => '4 Stars', 5 => '5 Stars']),

                Tables\Filters\Filter::make('is_hidden')
                    ->label('Hidden')
                    ->query(fn (Builder $query): Builder => $query->hidden())
                    ->toggle(),

                Tables\Filters\Filter::make('requires_moderation')
                    ->label('Requires Moderation')
                    ->query(fn (Builder $query): Builder => $query->pending())
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

                Tables\Filters\Filter::make('recently_updated')
                    ->label('Recently Updated')
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(7)))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status === ReviewStatus::PENDING->value)
                    ->action(fn (Review $record) => $record->update(['status' => ReviewStatus::APPROVED->value])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status === ReviewStatus::PENDING->value)
                    ->action(fn (Review $record) => $record->update(['status' => ReviewStatus::REJECTED->value])),
                Tables\Actions\Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => ! $record->is_hidden)
                    ->action(fn (Review $record) => $record->update(['is_hidden' => true])),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->is_hidden)
                    ->action(fn (Review $record) => $record->update(['is_hidden' => false])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => ReviewStatus::APPROVED->value]);
                            Notification::make()->title('Reviews approved')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => ReviewStatus::REJECTED->value]);
                            Notification::make()->title('Reviews rejected')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('hide')
                        ->label('Hide')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_hidden' => true]);
                            Notification::make()->title('Reviews hidden')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('restore')
                        ->label('Restore')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_hidden' => false]);
                            Notification::make()->title('Reviews restored')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Review $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No reviews found')
            ->emptyStateDescription('Reviews will appear here once customers submit them.')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'product']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
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
