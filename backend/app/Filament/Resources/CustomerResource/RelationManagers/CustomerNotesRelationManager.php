<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\CustomerNoteType;
use App\Models\CustomerNote;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'customerNotes';

    protected static ?string $title = 'Notes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(collect(CustomerNoteType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                    ->default(CustomerNoteType::GENERAL->value)
                    ->required(),

                Forms\Components\Toggle::make('is_pinned')
                    ->label('Pinned')
                    ->default(false),

                Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-pin')
                    ->trueColor('warning'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (CustomerNoteType $state): string => $state->color()),

                Tables\Columns\TextColumn::make('content')
                    ->wrap()
                    ->limit(120),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort(fn ($query) => $query->orderByDesc('is_pinned')->orderByDesc('created_at'))
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(CustomerNoteType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->after(function (CustomerNote $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_note.created',
                            $record,
                            ['customer_id' => $record->customer_id, 'type' => $record->type->value]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (CustomerNote $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_note.updated',
                            $record,
                            ['customer_id' => $record->customer_id, 'type' => $record->type->value]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (CustomerNote $record) {
                        AuditService::log(
                            auth()->user(),
                            'customer_note.deleted',
                            $record,
                            ['customer_id' => $record->customer_id]
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
