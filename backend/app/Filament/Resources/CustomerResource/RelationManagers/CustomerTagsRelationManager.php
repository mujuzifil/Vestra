<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\CustomerTag;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerTagsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerTags';

    protected static ?string $title = 'Tags';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_tag_id')
                    ->label('Tag')
                    ->options(fn () => CustomerTag::active()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All tags')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelect(fn (Forms\Components\Select $select) => $select->options(CustomerTag::active()->pluck('name', 'id')))
                    ->after(function () {
                        /** @var \App\Models\User $customer */
                        $customer = $this->getOwnerRecord();

                        AuditService::log(
                            auth()->user(),
                            'customer.tag_attached',
                            $customer,
                            ['tags' => $customer->customerTags()->pluck('name')]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->after(function () {
                        /** @var \App\Models\User $customer */
                        $customer = $this->getOwnerRecord();

                        AuditService::log(
                            auth()->user(),
                            'customer.tag_detached',
                            $customer,
                            ['tags' => $customer->customerTags()->pluck('name')]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
