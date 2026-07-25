<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditAccountResource\Pages;
use App\Models\CreditAccount;
use App\Services\AuditService;
use App\Services\CreditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreditAccountResource extends Resource
{
    protected static ?string $model = CreditAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Credit Accounts';

    protected static ?string $modelLabel = 'Credit Account';

    protected static ?string $pluralModelLabel = 'Credit Accounts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('distributor_id')
                    ->relationship('distributor', 'company_name')
                    ->required(),

                Forms\Components\TextInput::make('limit')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\TextInput::make('authorized_amount')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'suspended' => 'Suspended',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('admin_notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('distributor.company_name')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('limit')
                    ->label('Credit Limit')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Outstanding Balance')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('availableCredit')
                    ->label('Available Credit')
                    ->money('UGX')
                    ->getStateUsing(fn (CreditAccount $record): float => $record->availableCredit())
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Distributor name...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;

                        if (! $term) {
                            return $query;
                        }

                        return $query->whereHas('distributor', fn (Builder $q) => $q
                            ->where('company_name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('adjustCredit')
                    ->label('Adjust Credit')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->modalHeading('Adjust Credit')
                    ->modalDescription('Modify the distributor credit limit or outstanding balance.')
                    ->form([
                        Forms\Components\Select::make('target')
                            ->label('Adjustment Target')
                            ->options([
                                'limit' => 'Credit Limit',
                                'balance' => 'Outstanding Balance',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('operation')
                            ->label('Operation')
                            ->options([
                                'add' => 'Add / Increase',
                                'subtract' => 'Subtract / Decrease',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0.01)
                            ->required(),

                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, CreditAccount $record): void {
                        $service = app(CreditService::class);
                        $signed = $data['operation'] === 'subtract' ? -((float) $data['amount']) : (float) $data['amount'];

                        if ($data['target'] === 'limit') {
                            $newLimit = max(0, (float) $record->limit + $signed);
                            $service->updateLimit($record, $newLimit, $data['reason']);

                            Notification::make()
                                ->title('Credit limit adjusted')
                                ->success()
                                ->send();

                            return;
                        }

                        $newBalance = (float) $record->balance + $signed;

                        if ($newBalance < 0) {
                            Notification::make()
                                ->title('Insufficient outstanding balance')
                                ->body('The subtracted amount exceeds the current outstanding balance.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $service->addTransaction(
                            $record,
                            'adjustment',
                            $signed,
                            $data['reason'],
                            ['created_by' => auth()->id()]
                        );

                        Notification::make()
                            ->title('Outstanding balance adjusted')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(fn (CreditAccount $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No credit accounts found')
            ->emptyStateDescription('Credit accounts will appear once distributor onboarding is completed.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Distributor')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Infolists\Components\TextEntry::make('distributor.company_name')
                            ->label('Business Name'),

                        Infolists\Components\TextEntry::make('distributor.email')
                            ->label('Email'),

                        Infolists\Components\TextEntry::make('distributor.phone')
                            ->label('Phone'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Credit Summary')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Infolists\Components\TextEntry::make('limit')
                            ->label('Credit Limit')
                            ->money('UGX'),

                        Infolists\Components\TextEntry::make('balance')
                            ->label('Outstanding Balance')
                            ->money('UGX'),

                        Infolists\Components\TextEntry::make('authorized_amount')
                            ->label('Authorized Amount')
                            ->money('UGX'),

                        Infolists\Components\TextEntry::make('availableCredit')
                            ->label('Available Credit')
                            ->money('UGX')
                            ->getStateUsing(fn (CreditAccount $record): float => $record->availableCredit()),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('utilizationPercentage')
                            ->label('Utilization')
                            ->suffix('%')
                            ->getStateUsing(fn (CreditAccount $record): float => $record->utilizationPercentage()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Internal Notes')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),
            ]);
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
            'index' => Pages\ListCreditAccounts::route('/'),
            'view' => Pages\ViewCreditAccount::route('/{record}'),
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
