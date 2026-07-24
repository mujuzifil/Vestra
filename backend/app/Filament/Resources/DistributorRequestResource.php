<?php

namespace App\Filament\Resources;

use App\Enums\DistributorStatus;
use App\Enums\Priority;
use App\Filament\Resources\DistributorRequestResource\Pages;
use App\Models\DistributorRequest;
use App\Services\DistributorOnboardingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DistributorRequestResource extends Resource
{
    protected static ?string $model = DistributorRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Requests';
    protected static ?string $navigationLabel = 'Distributor Requests';
    protected static ?string $label = 'Distributor Request';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Information')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Business Name')
                            ->disabled(),
                        Forms\Components\TextInput::make('business_type')
                            ->disabled(),
                        Forms\Components\TextInput::make('years_in_operation')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Primary Contact')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Business Address')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('country')
                            ->disabled(),
                        Forms\Components\TextInput::make('region')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Business Details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('business_description')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('products_interested_in')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('target_region')
                            ->disabled(),
                        Forms\Components\TextInput::make('estimated_volume')
                            ->disabled(),
                        Forms\Components\Toggle::make('existing_customer')
                            ->disabled(),
                        Forms\Components\TextInput::make('previous_applications')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Review Decision')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                DistributorStatus::PENDING->value => DistributorStatus::PENDING->label(),
                                DistributorStatus::UNDER_REVIEW->value => DistributorStatus::UNDER_REVIEW->label(),
                                DistributorStatus::INFORMATION_REQUESTED->value => DistributorStatus::INFORMATION_REQUESTED->label(),
                                DistributorStatus::APPROVED->value => DistributorStatus::APPROVED->label(),
                                DistributorStatus::REJECTED->value => DistributorStatus::REJECTED->label(),
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
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned Administrator')
                            ->relationship('assignedAdministrator', 'name')
                            ->placeholder('Unassigned')
                            ->native(false),
                        Forms\Components\Textarea::make('internal_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\ViewColumn::make('applicant')
                    ->label('Applicant')
                    ->view('filament.tables.columns.distributor-applicant')
                    ->alignment('left'),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('No phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('region')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state instanceof DistributorStatus ? $state->label() : ucfirst($state))
                    ->color(fn ($state): string => $state instanceof DistributorStatus ? $state->color() : 'gray'),

                Tables\Columns\BadgeColumn::make('priority')
                    ->formatStateUsing(fn (string $state): string => Priority::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn (string $state): string => Priority::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('assignedAdministrator.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Business, contact, email, address...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('company_name', 'like', "%{$term}%")
                              ->orWhere('contact_person', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%")
                              ->orWhere('address', 'like', "%{$term}%")
                              ->orWhere('business_description', 'like', "%{$term}%");
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        DistributorStatus::PENDING->value => DistributorStatus::PENDING->label(),
                        DistributorStatus::UNDER_REVIEW->value => DistributorStatus::UNDER_REVIEW->label(),
                        DistributorStatus::INFORMATION_REQUESTED->value => DistributorStatus::INFORMATION_REQUESTED->label(),
                        DistributorStatus::APPROVED->value => DistributorStatus::APPROVED->label(),
                        DistributorStatus::REJECTED->value => DistributorStatus::REJECTED->label(),
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        Priority::CRITICAL->value => Priority::CRITICAL->label(),
                        Priority::HIGH->value => Priority::HIGH->label(),
                        Priority::MEDIUM->value => Priority::MEDIUM->label(),
                        Priority::LOW->value => Priority::LOW->label(),
                        Priority::NEUTRAL->value => Priority::NEUTRAL->label(),
                    ]),

                Tables\Filters\Filter::make('awaiting_review')
                    ->label('Awaiting Review')
                    ->query(fn (Builder $query): Builder => $query->awaitingReview())
                    ->toggle(),

                Tables\Filters\Filter::make('approved')
                    ->label('Approved')
                    ->query(fn (Builder $query): Builder => $query->approved())
                    ->toggle(),

                Tables\Filters\Filter::make('rejected')
                    ->label('Rejected')
                    ->query(fn (Builder $query): Builder => $query->rejected())
                    ->toggle(),

                Tables\Filters\Filter::make('recently_submitted')
                    ->label('Recently Submitted')
                    ->query(fn (Builder $query): Builder => $query->recentlySubmitted(7))
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
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DistributorRequest $record): bool => $record->status !== DistributorStatus::APPROVED)
                    ->action(function (DistributorRequest $record) {
                        app(DistributorOnboardingService::class)->approve($record, auth()->user());
                        Notification::make()->title('Application approved and distributor account created.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (DistributorRequest $record): bool => $record->status !== DistributorStatus::REJECTED)
                    ->action(fn (DistributorRequest $record) => $record->update(['status' => DistributorStatus::REJECTED])),
                Tables\Actions\Action::make('requestInformation')
                    ->label('Request Info')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (DistributorRequest $record): bool => ! in_array($record->status->value, [DistributorStatus::APPROVED->value, DistributorStatus::REJECTED->value, DistributorStatus::INFORMATION_REQUESTED->value], true))
                    ->action(fn (DistributorRequest $record) => $record->update(['status' => DistributorStatus::INFORMATION_REQUESTED])),
                Tables\Actions\Action::make('returnToReview')
                    ->label('Return to Review')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (DistributorRequest $record): bool => in_array($record->status->value, [DistributorStatus::PENDING->value, DistributorStatus::INFORMATION_REQUESTED->value], true))
                    ->action(fn (DistributorRequest $record) => $record->update(['status' => DistributorStatus::UNDER_REVIEW])),
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
                            $service = app(DistributorOnboardingService::class);
                            $records->each(fn (DistributorRequest $record) => $service->approve($record, auth()->user()));
                            Notification::make()->title('Applications approved and distributor accounts created.')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => DistributorStatus::REJECTED->value]);
                            Notification::make()->title('Applications rejected')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('requestInformation')
                        ->label('Request Information')
                        ->icon('heroicon-o-question-mark-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => DistributorStatus::INFORMATION_REQUESTED->value]);
                            Notification::make()->title('Information requested')->info()->send();
                        }),
                    Tables\Actions\BulkAction::make('returnToReview')
                        ->label('Return to Review')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => DistributorStatus::UNDER_REVIEW->value]);
                            Notification::make()->title('Applications returned to review')->warning()->send();
                        }),
                    Tables\Actions\BulkAction::make('assignReviewer')
                        ->label('Assign Reviewer')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Assign Reviewer')
                        ->modalDescription('Reviewer assignment workflow will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Assignment workflow is planned')->info()->send();
                        }),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Export Applications')
                        ->modalDescription('Export functionality will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Export integration is planned')->info()->send();
                        }),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Archive Applications')
                        ->modalDescription('Archiving will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Archiving integration is planned')->info()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (DistributorRequest $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No distributor requests found')
            ->emptyStateDescription('Distributor applications will appear here once they are submitted.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('assignedAdministrator');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributorRequests::route('/'),
            'view' => Pages\ViewDistributorRequest::route('/{record}'),
            'edit' => Pages\EditDistributorRequest::route('/{record}/edit'),
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
