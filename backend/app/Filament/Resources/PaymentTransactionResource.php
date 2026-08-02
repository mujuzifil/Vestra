<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Exports\PaymentTransactionExporter;
use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\PaymentTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Payment Transactions';

    protected static ?string $modelLabel = 'Payment Transaction';

    protected static ?string $pluralModelLabel = 'Payment Transactions';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'invoice_number')
                    ->required(),

                Forms\Components\TextInput::make('payment_method')
                    ->required(),

                Forms\Components\TextInput::make('provider')
                    ->required(),

                Forms\Components\TextInput::make('transaction_reference')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\TextInput::make('currency')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('paid_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.invoice_number')
                    ->label('Order / Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('transaction_reference')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn (string $state): string => PaymentMethod::tryFrom($state)?->label() ?? ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Transaction ID, invoice...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;

                        if (! $term) {
                            return $query;
                        }

                        return $query
                            ->where('transaction_reference', 'like', "%{$term}%")
                            ->orWhere('provider_reference', 'like', "%{$term}%")
                            ->orWhereHas('order', fn (Builder $q) => $q->where('invoice_number', 'like', "%{$term}%"));
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn ($method) => [$method->value => $method->label()])),

                Tables\Filters\Filter::make('paid_at')
                    ->label('Paid Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q) => $q->whereDate('paid_at', '>=', $data['from']))
                            ->when($data['until'] ?? null, fn (Builder $q) => $q->whereDate('paid_at', '<=', $data['until']));
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->label('Export')
                        ->exporter(PaymentTransactionExporter::class),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(PaymentTransactionExporter::class),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (PaymentTransaction $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No payment transactions found')
            ->emptyStateDescription('Payments processed through Flutterwave or recorded manually will appear here.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Reference')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Infolists\Components\TextEntry::make('order.invoice_number')
                            ->label('Invoice Number'),

                        Infolists\Components\TextEntry::make('order.user.name')
                            ->label('Customer'),

                        Infolists\Components\TextEntry::make('order.total_amount')
                            ->label('Order Total')
                            ->money('UGX'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Payment Details')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_reference')
                            ->label('Transaction ID'),

                        Infolists\Components\TextEntry::make('provider_reference')
                            ->label('Provider Reference')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('amount')
                            ->money('UGX'),

                        Infolists\Components\TextEntry::make('currency')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->formatStateUsing(fn (string $state): string => PaymentMethod::tryFrom($state)?->label() ?? ucfirst($state)),

                        Infolists\Components\TextEntry::make('provider')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'gray',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Provider Response')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        Infolists\Components\TextEntry::make('response_data')
                            ->label('Response Data')
                            ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_PRETTY_PRINT) : '{}')
                            ->extraAttributes(['class' => 'font-mono text-sm'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
