<?php

namespace App\Filament\Resources;

use App\Enums\PaymentUploadStatus;
use App\Filament\Resources\PaymentUploadResource\Pages;
use App\Models\CreditAccount;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\PaymentUpload;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PaymentUploadResource extends Resource
{
    protected static ?string $model = PaymentUpload::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Payment Uploads';

    protected static ?string $modelLabel = 'Payment Upload';

    protected static ?string $pluralModelLabel = 'Payment Uploads';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('distributor_id')
                    ->relationship('distributor', 'company_name')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('UGX')
                    ->required(),

                Forms\Components\TextInput::make('currency')
                    ->required(),

                Forms\Components\TextInput::make('reference_number')
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Proof of Payment')
                    ->disk('public')
                    ->required(),

                Forms\Components\Textarea::make('notes')
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

                Tables\Columns\TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (PaymentUploadStatus $state): string => $state->label())
                    ->color(fn (PaymentUploadStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime('M d, Y H:i')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PaymentUploadStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])),

                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Distributor, reference...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;

                        if (! $term) {
                            return $query;
                        }

                        return $query
                            ->where('reference_number', 'like', "%{$term}%")
                            ->orWhereHas('distributor', fn (Builder $q) => $q
                                ->where('company_name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%"));
                    }),

                Tables\Filters\Filter::make('uploaded_at')
                    ->label('Uploaded Date')
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

                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PaymentUpload $record): bool => $record->status !== PaymentUploadStatus::VERIFIED)
                    ->modalHeading('Verify Payment Upload')
                    ->form([
                        Forms\Components\Select::make('record_with')
                            ->label('Record Verification As')
                            ->options([
                                'none' => 'Do not create a transaction',
                                'payment_transaction' => 'Create Payment Transaction',
                                'credit_transaction' => 'Create Credit Transaction',
                            ])
                            ->default('none')
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('distributor.orders', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => $get('record_with') === 'payment_transaction')
                            ->required(fn (Forms\Get $get): bool => $get('record_with') === 'payment_transaction'),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, PaymentUpload $record): void {
                        $record->update([
                            'status' => PaymentUploadStatus::VERIFIED,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'verification_notes' => $data['verification_notes'] ?? null,
                        ]);

                        if (($data['record_with'] ?? 'none') === 'payment_transaction') {
                            PaymentTransaction::create([
                                'order_id' => $data['order_id'],
                                'payment_method' => 'bank_transfer',
                                'provider' => 'manual',
                                'transaction_reference' => $record->reference_number,
                                'provider_reference' => $record->reference_number,
                                'amount' => $record->amount,
                                'currency' => $record->currency ?: 'UGX',
                                'status' => 'paid',
                                'paid_at' => now(),
                            ]);
                        }

                        if (($data['record_with'] ?? 'none') === 'credit_transaction') {
                            $creditAccount = CreditAccount::firstOrCreate(
                                ['distributor_id' => $record->distributor_id],
                                ['limit' => 0, 'balance' => 0, 'authorized_amount' => 0, 'status' => 'active']
                            );

                            app(CreditService::class)->addTransaction(
                                $creditAccount,
                                'payment',
                                -((float) $record->amount),
                                "Bank transfer verified: {$record->reference_number}",
                                ['created_by' => auth()->id()]
                            );
                        }

                        AuditService::log(auth()->user(), 'payment_upload.verified', $record, [
                            'reference_number' => $record->reference_number,
                            'amount' => (float) $record->amount,
                            'record_with' => $data['record_with'] ?? 'none',
                        ]);

                        Notification::make()
                            ->title('Payment upload verified')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (PaymentUpload $record): bool => $record->status !== PaymentUploadStatus::REJECTED)
                    ->modalHeading('Reject Payment Upload')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, PaymentUpload $record): void {
                        $record->update([
                            'status' => PaymentUploadStatus::REJECTED,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'verification_notes' => $data['reason'],
                        ]);

                        AuditService::log(auth()->user(), 'payment_upload.rejected', $record, [
                            'reference_number' => $record->reference_number,
                            'reason' => $data['reason'],
                        ]);

                        Notification::make()
                            ->title('Payment upload rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verify Selected Uploads')
                        ->modalDescription('Mark all selected uploads as verified without creating transactions.')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => PaymentUploadStatus::VERIFIED,
                                    'verified_by' => auth()->id(),
                                    'verified_at' => now(),
                                ]);

                                AuditService::log(auth()->user(), 'payment_upload.verified', $record, [
                                    'reference_number' => $record->reference_number,
                                    'amount' => (float) $record->amount,
                                    'bulk' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Selected uploads verified')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Selected Uploads')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => PaymentUploadStatus::REJECTED,
                                    'verified_by' => auth()->id(),
                                    'verified_at' => now(),
                                    'verification_notes' => $data['reason'],
                                ]);

                                AuditService::log(auth()->user(), 'payment_upload.rejected', $record, [
                                    'reference_number' => $record->reference_number,
                                    'reason' => $data['reason'],
                                    'bulk' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Selected uploads rejected')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (PaymentUpload $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No payment uploads found')
            ->emptyStateDescription('Distributor bank transfer proofs will appear here once uploaded.');
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

                Infolists\Components\Section::make('Payment Details')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->money('UGX'),

                        Infolists\Components\TextEntry::make('currency')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('reference_number')
                            ->label('Reference Number'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (PaymentUploadStatus $state): string => $state->color()),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Uploaded At')
                            ->dateTime('M d, Y H:i'),

                        Infolists\Components\TextEntry::make('file_path')
                            ->label('Proof File')
                            ->formatStateUsing(fn (?string $state): string => $state ? 'Uploaded' : '—')
                            ->url(fn (PaymentUpload $record): ?string => $record->fileUrl(), true),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Verification')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Infolists\Components\TextEntry::make('verifier.name')
                            ->label('Verified By')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('verified_at')
                            ->label('Verified At')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('verification_notes')
                            ->label('Verification Notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Notes')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['distributor', 'verifier']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentUploads::route('/'),
            'view' => Pages\ViewPaymentUpload::route('/{record}'),
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
