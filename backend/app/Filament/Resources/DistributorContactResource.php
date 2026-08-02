<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorContactResource\Pages;
use App\Models\DistributorContact;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorContactResource extends Resource
{
    protected static ?string $model = DistributorContact::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Contacts';

    protected static ?string $label = 'Contact';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact Details')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('distributor_id')
                            ->label('Distributor')
                            ->relationship('distributor', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('distributor.company_name')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold'),

                Tables\Columns\TextColumn::make('role')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),

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

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Contact'),
            ])
            ->filtersFormColumns(2)
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                AuditService::log(
                                    auth()->user(),
                                    'distributor_contact.deleted',
                                    $record,
                                    ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                                );
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListDistributorContacts::route('/'),
            'create' => Pages\CreateDistributorContact::route('/create'),
            'edit' => Pages\EditDistributorContact::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
