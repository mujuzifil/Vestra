<?php

namespace App\Filament\Resources\DistributorResource\RelationManagers;

use App\Models\DistributorDocument;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document')
                    ->icon('heroicon-o-document')
                    ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

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
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Title or type...'),
                    ])
                    ->query(function ($query, array $data) {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function ($q) use ($term) {
                            $q->where('title', 'like', "%{$term}%")
                                ->orWhere('type', 'like', "%{$term}%");
                        });
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();

                        return $data;
                    })
                    ->after(function (DistributorDocument $record) {
                        AuditService::log(
                            auth()->user(),
                            'distributor_document.created',
                            $record,
                            ['distributor_id' => $record->distributor_id, 'title' => $record->title]
                        );
                    }),
            ])
            ->actions([
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
