<?php

namespace App\Filament\Resources;

use App\Enums\PurchaseOrderStatus;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Purchase Orders';

    protected static ?string $modelLabel = 'Purchase Order';

    protected static ?string $pluralModelLabel = 'Purchase Orders';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Order')
                    ->schema([
                        Forms\Components\TextInput::make('po_number')
                            ->label('PO Number')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->options(collect(PurchaseOrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required(),

                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('UGX'),

                        Forms\Components\DatePicker::make('ordered_at'),

                        Forms\Components\DatePicker::make('expected_at'),

                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PurchaseOrderStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('total')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('ordered_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_at')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PurchaseOrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supplier', 'warehouse']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['po_number', 'notes'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->po_number;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Supplier' => $record->supplier?->name ?? '—',
            'Status' => $record->status->label(),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
