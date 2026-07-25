<?php

namespace App\Filament\Resources\DistributorResource\RelationManagers;

use App\Models\DistributorBranch;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $title = 'Branches';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branch Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Kampala Branch'),

                        Forms\Components\TextInput::make('manager_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Branch'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('district')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->placeholder('e.g. 0.3476'),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->placeholder('e.g. 32.5825'),

                        Forms\Components\Textarea::make('delivery_notes')
                            ->rows(2)
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

                Tables\Columns\TextColumn::make('manager_name')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('city')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (DistributorBranch $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_branch.created',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DistributorBranch $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_branch.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorBranch $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_branch.deleted',
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
