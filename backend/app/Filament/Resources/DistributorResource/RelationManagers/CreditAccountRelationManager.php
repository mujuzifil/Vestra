<?php

namespace App\Filament\Resources\DistributorResource\RelationManagers;

use App\Models\CreditAccount;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CreditAccountRelationManager extends RelationManager
{
    protected static string $relationship = 'creditAccount';

    protected static ?string $title = 'Credit Account';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Credit Details')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\TextInput::make('limit')
                            ->label('Credit Limit')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('balance')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('authorized_amount')
                            ->label('Authorized Amount')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('admin_notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('limit')
                    ->label('Credit Limit')
                    ->money('UGX'),

                Tables\Columns\TextColumn::make('balance')
                    ->money('UGX'),

                Tables\Columns\TextColumn::make('authorized_amount')
                    ->label('Authorized')
                    ->money('UGX'),

                Tables\Columns\TextColumn::make('availableCredit')
                    ->label('Available')
                    ->money('UGX')
                    ->state(fn (CreditAccount $record): float => $record->availableCredit()),

                Tables\Columns\TextColumn::make('utilizationPercentage')
                    ->label('Utilization')
                    ->formatStateUsing(fn (CreditAccount $record): string => number_format($record->utilizationPercentage(), 2) . '%'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([])
            ->headerActions([
                // Credit account is created automatically; edit only.
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (CreditAccount $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_credit_account.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'limit' => $record->limit]
                        );
                    }),
            ])
            ->bulkActions([]);
    }
}
