<?php

namespace App\Filament\Resources;

use App\Enums\CreditTransactionType;
use App\Filament\Resources\CreditTransactionResource\Pages;
use App\Models\CreditTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreditTransactionResource extends Resource
{
    protected static ?string $model = CreditTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Credit Transactions';

    protected static ?string $modelLabel = 'Credit Transaction';

    protected static ?string $pluralModelLabel = 'Credit Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('credit_account_id')
                    ->relationship('creditAccount.distributor', 'company_name')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->options(collect(CreditTransactionType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\TextInput::make('balance_after')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (CreditTransactionType $state): string => $state->label())
                    ->color(fn (CreditTransactionType $state): string => match ($state) {
                        CreditTransactionType::PAYMENT => 'success',
                        CreditTransactionType::ADJUSTMENT => 'warning',
                        CreditTransactionType::LIMIT_CHANGE => 'info',
                        CreditTransactionType::AUTHORIZATION => 'primary',
                        CreditTransactionType::CAPTURE => 'danger',
                        CreditTransactionType::RELEASE => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Balance')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(fn (CreditTransaction $record): ?string => $record->description)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('credit_account_id')
                    ->label('Credit Account')
                    ->relationship('creditAccount.distributor', 'company_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(CreditTransactionType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])),

                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
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
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No credit transactions found')
            ->emptyStateDescription('Transactions will appear when credit accounts are used.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creditAccount.distributor', 'creator']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditTransactions::route('/'),
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

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
