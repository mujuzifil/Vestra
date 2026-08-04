<?php

namespace App\Filament\Resources;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?string $label = 'Blog Post';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Content')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name', fn (Builder $query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Textarea::make('excerpt')
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog-content'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->disk('public')
                            ->directory('blog/featured')
                            ->maxSize(5120)
                            ->nullable(),

                        Forms\Components\FileUpload::make('gallery')
                            ->image()
                            ->disk('public')
                            ->directory('blog/gallery')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                BlogPostStatus::DRAFT->value => BlogPostStatus::DRAFT->label(),
                                BlogPostStatus::PUBLISHED->value => BlogPostStatus::PUBLISHED->label(),
                                BlogPostStatus::SCHEDULED->value => BlogPostStatus::SCHEDULED->label(),
                                BlogPostStatus::ARCHIVED->value => BlogPostStatus::ARCHIVED->label(),
                            ])
                            ->native(false),

                        Forms\Components\Select::make('visibility')
                            ->required()
                            ->options([
                                BlogPostVisibility::PUBLIC->value => BlogPostVisibility::PUBLIC->label(),
                                BlogPostVisibility::INTERNAL->value => BlogPostVisibility::INTERNAL->label(),
                            ])
                            ->default(BlogPostVisibility::PUBLIC->value)
                            ->native(false),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured on Knowledge Centre')
                            ->default(false),

                        Forms\Components\TextInput::make('reading_time_minutes')
                            ->label('Reading time (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published at')
                            ->native(false)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled at')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Categorisation')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name', fn (Builder $query) => $query->where('is_active', true))
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name', fn (Builder $query) => $query->where('is_active', true))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(2000)
                            ->nullable(),

                        Forms\Components\TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(500)
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('author.name')
                    ->placeholder('No author')
                    ->sortable(),

                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BlogPostStatus $state): string => $state->label())
                    ->color(fn (BlogPostStatus $state): string => $state->color()),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        BlogPostStatus::DRAFT->value => BlogPostStatus::DRAFT->label(),
                        BlogPostStatus::PUBLISHED->value => BlogPostStatus::PUBLISHED->label(),
                        BlogPostStatus::SCHEDULED->value => BlogPostStatus::SCHEDULED->label(),
                        BlogPostStatus::ARCHIVED->value => BlogPostStatus::ARCHIVED->label(),
                    ]),
                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        BlogPostVisibility::PUBLIC->value => BlogPostVisibility::PUBLIC->label(),
                        BlogPostVisibility::INTERNAL->value => BlogPostVisibility::INTERNAL->label(),
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'view' => Pages\ViewBlogPost::route('/{record}'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
