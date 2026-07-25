<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorDocumentResource\Pages;
use App\Models\DistributorDocument;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistributorDocumentResource extends Resource
{
    protected static ?string $model = DistributorDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $label = 'Document';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document')
                    ->icon('heroicon-o-document')
                    ->schema([
                        Forms\Components\Select::make('distributor_id')
                            ->label('Distributor')
                            ->relationship('distributor', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('type')
                            ->maxLength(255)
                            ->placeholder('e.g. Certificate of Incorporation, Tax Clearance'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document File')
                            ->directory('distributor-documents')
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('version')
                            ->maxLength(255)
                            ->placeholder('e.g. 1.0'),
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

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold'),

                Tables\Columns\TextColumn::make('type')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('version')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'company_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Title or type...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('title', 'like', "%{$term}%")
                                ->orWhere('type', 'like', "%{$term}%");
                        });
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (DistributorDocument $record): string => $record->fileUrl())
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->after(function (DistributorDocument $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_document.updated',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'title' => $record->title]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (DistributorDocument $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_document.deleted',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'title' => $record->title]
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
                                    'distributor_document.deleted',
                                    $record,
                                    ['distributor_id' => $record->distributor_id, 'title' => $record->title]
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
            ->with(['distributor', 'uploader']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistributorDocuments::route('/'),
            'view' => Pages\ViewDistributorDocument::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        // Documents are managed from the distributor detail view relation manager.
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
