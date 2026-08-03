<?php

namespace App\Filament\Resources;

use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quotes';

    protected static ?string $label = 'Quote Request';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('company_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('district')
                            ->disabled(),
                        Forms\Components\TextInput::make('city')
                            ->disabled(),
                        Forms\Components\Textarea::make('address')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Delivery & Requirements')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\DatePicker::make('preferred_delivery_date')
                            ->disabled(),
                        Forms\Components\Textarea::make('delivery_location')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('requirements')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Items Requested')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->disabled()
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('product_name'),
                                Forms\Components\TextInput::make('package_size'),
                                Forms\Components\TextInput::make('quantity')->numeric(),
                                Forms\Components\Textarea::make('notes')->rows(2),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Forms\Components\Section::make('CRM')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ])
                            ->native(false)
                            ->nullable(),
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()
                            ->prefix('UGX')
                            ->nullable(),
                        Forms\Components\DatePicker::make('expected_close_date')
                            ->native(false)
                            ->nullable(),
                        Forms\Components\KeyValue::make('crm_metadata')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Attachments')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\ViewField::make('attachments')
                            ->view('filament.components.quote-request-attachments')
                            ->visible(fn ($record) => filled($record?->attachments)),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Admin Handling')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(QuoteRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(fn () => User::where('is_admin', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->nullable(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Metadata')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->disabled(),
                        Forms\Components\TextInput::make('source')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Submitted At')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state instanceof QuoteRequestStatus ? $state->label() : ucfirst($state))
                    ->color(fn ($state): string => $state instanceof QuoteRequestStatus ? $state->color() : 'gray'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(QuoteRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload(),

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
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('markContacted')
                    ->label('Mark Contacted')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (QuoteRequest $record): bool => $record->status === QuoteRequestStatus::PENDING)
                    ->action(function (QuoteRequest $record): void {
                        $record->update(['status' => QuoteRequestStatus::CONTACTED->value]);
                        Notification::make()->title('Quote request marked as contacted')->success()->send();
                    }),

                Tables\Actions\Action::make('markQuoted')
                    ->label('Mark Quoted')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (QuoteRequest $record): bool => in_array($record->status, [QuoteRequestStatus::PENDING, QuoteRequestStatus::CONTACTED], true))
                    ->action(function (QuoteRequest $record): void {
                        $record->update(['status' => QuoteRequestStatus::QUOTED->value]);
                        Notification::make()->title('Quote request marked as quoted')->success()->send();
                    }),

                Tables\Actions\Action::make('markApproved')
                    ->label('Mark Approved')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (QuoteRequest $record): bool => $record->status !== QuoteRequestStatus::APPROVED)
                    ->action(function (QuoteRequest $record): void {
                        $record->update(['status' => QuoteRequestStatus::APPROVED->value]);
                        Notification::make()->title('Quote request marked as approved')->success()->send();
                    }),

                Tables\Actions\Action::make('markDeclined')
                    ->label('Mark Declined')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (QuoteRequest $record): bool => $record->status !== QuoteRequestStatus::DECLINED)
                    ->action(function (QuoteRequest $record): void {
                        $record->update(['status' => QuoteRequestStatus::DECLINED->value]);
                        Notification::make()->title('Quote request marked as declined')->success()->send();
                    }),

                Tables\Actions\Action::make('markClosed')
                    ->label('Mark Closed')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (QuoteRequest $record): bool => $record->status !== QuoteRequestStatus::CLOSED)
                    ->action(function (QuoteRequest $record): void {
                        $record->update(['status' => QuoteRequestStatus::CLOSED->value]);
                        Notification::make()->title('Quote request marked as closed')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markContacted')
                        ->label('Mark Contacted')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (QuoteRequest $record) => $record->update(['status' => QuoteRequestStatus::CONTACTED->value]));
                            Notification::make()->title('Selected quote requests marked as contacted')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markQuoted')
                        ->label('Mark Quoted')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (QuoteRequest $record) => $record->update(['status' => QuoteRequestStatus::QUOTED->value]));
                            Notification::make()->title('Selected quote requests marked as quoted')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('markClosed')
                        ->label('Mark Closed')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (QuoteRequest $record) => $record->update(['status' => QuoteRequestStatus::CLOSED->value]));
                            Notification::make()->title('Selected quote requests marked as closed')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (QuoteRequest $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No quote requests found')
            ->emptyStateDescription('Quote requests submitted through the website will appear here.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['items', 'assignedUser']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteRequests::route('/'),
            'view' => Pages\ViewQuoteRequest::route('/{record}'),
            'edit' => Pages\EditQuoteRequest::route('/{record}/edit'),
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
