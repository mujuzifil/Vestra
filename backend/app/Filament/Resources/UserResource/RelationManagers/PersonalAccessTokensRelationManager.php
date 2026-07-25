<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessTokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    protected static ?string $title = 'API Tokens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('abilities')
                    ->multiple()
                    ->options([
                        '*' => 'All abilities',
                        'read' => 'Read',
                        'write' => 'Write',
                    ])
                    ->default(['*']),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires at'),
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
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('abilities')
                    ->badge()
                    ->formatStateUsing(fn (array $state): string => implode(', ', $state))
                    ->wrap(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->since()
                    ->sortable()
                    ->placeholder('Never used'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No expiry'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('expired')
                    ->label('Expired')
                    ->placeholder('All tokens')
                    ->trueLabel('Expired')
                    ->falseLabel('Active')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now())),
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['token'] = \Illuminate\Support\Str::random(80);

                        return $data;
                    })
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
