<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\CustomerAddress;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Address Details')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Home, Office'),

                        Forms\Components\TextInput::make('full_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_line')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_line_2')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('region')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('district')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('delivery_notes')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_default_shipping')
                            ->label('Default shipping'),

                        Forms\Components\Toggle::make('is_default_billing')
                            ->label('Default billing'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address_line')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('city')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default_shipping')
                    ->label('Shipping')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_default_billing')
                    ->label('Billing')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default_shipping')
                    ->label('Default shipping'),

                Tables\Filters\TernaryFilter::make('is_default_billing')
                    ->label('Default billing'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (CustomerAddress $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_address.created',
                            $record,
                            ['customer_id' => $record->user_id, 'label' => $record->label]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (CustomerAddress $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_address.updated',
                            $record,
                            ['customer_id' => $record->user_id, 'label' => $record->label]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (CustomerAddress $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_address.deleted',
                            $record,
                            ['customer_id' => $record->user_id, 'label' => $record->label]
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
