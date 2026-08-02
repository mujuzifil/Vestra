<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogAuthorResource\Pages;
use App\Models\BlogAuthor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogAuthorResource extends Resource
{
    protected static ?string $model = BlogAuthor::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Blog Authors';

    protected static ?string $label = 'Blog Author';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
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

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('role')
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('e.g. Product Specialist'),

                Forms\Components\Textarea::make('bio')
                    ->maxLength(5000)
                    ->columnSpanFull()
                    ->nullable(),

                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->disk('public')
                    ->directory('blog/authors')
                    ->maxSize(5120)
                    ->nullable(),

                Forms\Components\KeyValue::make('social_links')
                    ->keyLabel('Platform')
                    ->valueLabel('URL')
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold'),

                Tables\Columns\TextColumn::make('role')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogAuthors::route('/'),
            'create' => Pages\CreateBlogAuthor::route('/create'),
            'edit' => Pages\EditBlogAuthor::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
