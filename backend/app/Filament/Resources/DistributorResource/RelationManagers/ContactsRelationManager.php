<?php

namespace App\Filament\Resources\DistributorResource\RelationManagers;

use App\Models\DistributorContact;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contacts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact Details')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('role')
                            ->maxLength(255)
                            ->placeholder('e.g. Procurement Manager'),

                        Forms\Components\TextInput::make('phone')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_primary')
                            ->label('Primary Contact'),

                        Forms\Components\KeyValue::make('permissions_json')
                            ->label('Permissions')
                            ->keyLabel('Permission')
                            ->valueLabel('Enabled')
                            ->default([
                                'orders' => true,
                                'quotes' => true,
                                'invoices' => true,
                                'payments' => true,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('role')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Contact'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (DistributorContact $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_contact.created',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DistributorContact $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_contact.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorContact $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_contact.deleted',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
