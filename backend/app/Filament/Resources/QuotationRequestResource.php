<?php

namespace App\Filament\Resources;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationRequestResource\Pages;
use App\Models\QuotationRequest;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class QuotationRequestResource extends Resource
{
    protected static ?string $model = QuotationRequest::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?string $label = 'Quotation Request';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quotation Information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Select::make('distributor_id')
                            ->label('Distributor')
                            ->relationship('distributor', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('status')
                            ->options(collect(QuotationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timeline & Amounts')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->label('Submitted At'),

                        Forms\Components\DateTimePicker::make('quoted_at')
                            ->label('Quoted At'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At'),

                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('distributor.company_name')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state instanceof QuotationStatus ? $state->label() : ucfirst($state))
                    ->color(fn ($state): string => $state instanceof QuotationStatus ? $state->color() : 'gray'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quoted_at')
                    ->label('Quoted')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'company_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(QuotationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\Filter::make('submitted_at')
                    ->label('Submitted Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q) => $q->whereDate('submitted_at', '>=', $data['from']))
                            ->when($data['until'] ?? null, fn (Builder $q) => $q->whereDate('submitted_at', '<=', $data['until']));
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (QuotationRequest $record): bool => in_array($record->status, [QuotationStatus::SUBMITTED, QuotationStatus::DRAFT], true))
                    ->action(function (QuotationRequest $record): void {
                        $record->update(['status' => QuotationStatus::REVIEWED]);
                        AuditService::log(auth()->user(), 'quotation.reviewed', $record, ['reference' => $record->reference_number]);
                        Notification::make()->title('Quotation marked as reviewed')->success()->send();
                    }),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (QuotationRequest $record): bool => in_array($record->status, [QuotationStatus::REVIEWED, QuotationStatus::QUOTED, QuotationStatus::SUBMITTED], true))
                    ->action(function (QuotationRequest $record): void {
                        $record->update(['status' => QuotationStatus::ACCEPTED]);
                        AuditService::log(auth()->user(), 'quotation.approved', $record, ['reference' => $record->reference_number]);
                        Notification::make()->title('Quotation approved')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (QuotationRequest $record): bool => ! in_array($record->status, [QuotationStatus::REJECTED, QuotationStatus::CONVERTED_TO_ORDER], true))
                    ->action(function (QuotationRequest $record): void {
                        $record->update(['status' => QuotationStatus::REJECTED]);
                        AuditService::log(auth()->user(), 'quotation.rejected', $record, ['reference' => $record->reference_number]);
                        Notification::make()->title('Quotation rejected')->success()->send();
                    }),

                Tables\Actions\Action::make('convertToOrder')
                    ->label('Convert to Order')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Quotation to Order')
                    ->modalDescription('This will convert the quotation into an order. Full conversion logic will be implemented in a future release.')
                    ->visible(fn (QuotationRequest $record): bool => in_array($record->status, [QuotationStatus::ACCEPTED, QuotationStatus::QUOTED], true))
                    ->action(function (QuotationRequest $record): void {
                        $record->update(['status' => QuotationStatus::CONVERTED_TO_ORDER]);
                        AuditService::log(auth()->user(), 'quotation.converted_to_order', $record, ['reference' => $record->reference_number]);
                        Notification::make()->title('Quotation converted to order (placeholder)')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                AuditService::log(
                                    auth()->user(),
                                    'quotation.deleted',
                                    $record,
                                    ['reference' => $record->reference_number]
                                );
                            }
                        }),

                    Tables\Actions\BulkAction::make('review')
                        ->label('Review Selected')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (QuotationRequest $record) {
                                if (in_array($record->status, [QuotationStatus::SUBMITTED, QuotationStatus::DRAFT], true)) {
                                    $record->update(['status' => QuotationStatus::REVIEWED]);
                                    AuditService::log(auth()->user(), 'quotation.reviewed', $record, ['reference' => $record->reference_number]);
                                }
                            });
                            Notification::make()->title('Selected quotations reviewed')->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (QuotationRequest $record) {
                                if (in_array($record->status, [QuotationStatus::REVIEWED, QuotationStatus::QUOTED, QuotationStatus::SUBMITTED], true)) {
                                    $record->update(['status' => QuotationStatus::ACCEPTED]);
                                    AuditService::log(auth()->user(), 'quotation.approved', $record, ['reference' => $record->reference_number]);
                                }
                            });
                            Notification::make()->title('Selected quotations approved')->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (QuotationRequest $record) {
                                if (! in_array($record->status, [QuotationStatus::REJECTED, QuotationStatus::CONVERTED_TO_ORDER], true)) {
                                    $record->update(['status' => QuotationStatus::REJECTED]);
                                    AuditService::log(auth()->user(), 'quotation.rejected', $record, ['reference' => $record->reference_number]);
                                }
                            });
                            Notification::make()->title('Selected quotations rejected')->success()->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (QuotationRequest $record): string => static::getUrl('view', ['record' => $record]))
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('distributor');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotationRequests::route('/'),
            'view' => Pages\ViewQuotationRequest::route('/{record}'),
            'edit' => Pages\EditQuotationRequest::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
