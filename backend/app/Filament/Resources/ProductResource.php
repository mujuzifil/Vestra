<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->icon('heroicon-o-shopping-bag')
                    ->description('Core product identifiers and visibility.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. VESTRA EcoSuit Cleaner')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the product URL. Auto-generated from the name.'),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->required()
                            ->options(Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. VST-CLN-001')
                            ->helperText('Unique stock keeping unit.'),

                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                ProductStatus::ACTIVE->value => ProductStatus::ACTIVE->label(),
                                ProductStatus::INACTIVE->value => ProductStatus::INACTIVE->label(),
                                ProductStatus::OUT_OF_STOCK->value => ProductStatus::OUT_OF_STOCK->label(),
                            ])
                            ->default(ProductStatus::ACTIVE->value)
                            ->native(false),

                        Forms\Components\Toggle::make('featured')
                            ->label('Featured product')
                            ->helperText('Featured products may be highlighted on the storefront.')
                            ->default(false)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->icon('heroicon-o-currency-dollar')
                    ->description('Base price and future pricing fields.')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Regular Price')
                            ->required()
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->placeholder('0.00'),

                        Forms\Components\TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Planned: sale price will be enabled when promotions are implemented.'),

                        Forms\Components\TextInput::make('compare_at_price')
                            ->label('Compare-at Price')
                            ->numeric()
                            ->prefix('UGX')
                            ->minValue(0)
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Planned: used to show a strikethrough reference price.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Inventory')
                    ->icon('heroicon-o-cube')
                    ->description('Stock levels and availability.')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Current Stock')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->helperText(fn ($state): string => is_numeric($state) && (int) $state <= 10
                                ? 'This product is at or below the low-stock threshold.'
                                : 'Available units in stock.'),

                        Forms\Components\Placeholder::make('stock_status_preview')
                            ->label('Stock Status')
                            ->content(function (?Product $record, $get): HtmlString {
                                $quantity = (int) ($get('stock_quantity') ?? $record?->stock_quantity ?? 0);
                                $label = match (true) {
                                    $quantity === 0 => 'Out of Stock',
                                    $quantity <= 5 => 'Low Stock',
                                    $quantity <= 10 => 'Running Low',
                                    default => 'In Stock',
                                };
                                $color = match (true) {
                                    $quantity === 0, $quantity <= 5 => 'danger',
                                    $quantity <= 10 => 'warning',
                                    default => 'success',
                                };

                                return new HtmlString("<span class=\"fi-badge fi-color-custom fi-color-{$color} inline-flex items-center justify-center rounded-md px-2 min-w-[theme(spacing.6)] text-xs font-medium ring-1 ring-inset \">{$label}</span>");
                            }),

                        Forms\Components\TextInput::make('reserved_stock')
                            ->label('Reserved Stock')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Planned: units reserved for pending orders.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->description('Product copy and long-form content.')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->label('Short Description')
                            ->rows(2)
                            ->maxLength(1000)
                            ->placeholder('A brief summary shown in listings.')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Full Description')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'orderedList',
                                'bulletList',
                                'blockquote',
                            ])
                            ->placeholder('Detailed product description...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media')
                    ->icon('heroicon-o-photo')
                    ->description('Product images, drag to reorder.')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                                    ->directory('products')
                                    ->required()
                                    ->imagePreviewHeight('120px')
                                    ->maxSize(5120)
                                    ->helperText('Accepted: PNG, JPG, WEBP. Max 5 MB.')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('Describe the image for accessibility and SEO.')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Hidden::make('sort_order')
                                    ->default(0),
                            ])
                            ->orderColumn('sort_order')
                            ->reorderableWithDragAndDrop()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? null)
                            ->columnSpanFull()
                            ->addActionLabel('Add image'),
                    ]),

                Forms\Components\Section::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->description('Search engine metadata and preview.')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->hint(fn ($state): string => strlen($state ?? '') . ' / 255')
                            ->placeholder('Recommended: 50–60 characters'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(1000)
                            ->live(onBlur: true)
                            ->hint(fn ($state): string => strlen($state ?? '') . ' / 1000')
                            ->placeholder('Recommended: 150–160 characters')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('seo_preview')
                            ->label('')
                            ->content(function (?Product $record, $get): HtmlString {
                                return new HtmlString(view('components.filament.vestra.seo-preview-card', [
                                    'slug' => $get('slug') ?: ($record->slug ?? ''),
                                    'title' => $get('meta_title') ?? '',
                                    'description' => $get('meta_description') ?? '',
                                ])->render());
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Publishing')
                    ->icon('heroicon-o-clock')
                    ->description('Record timestamps and audit trail.')
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn (?Product $record): string => $record?->created_at?->format('M d, Y H:i') ?? '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn (?Product $record): string => $record?->updated_at?->format('M d, Y H:i') ?? '-'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'images']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.image')
                    ->label('')
                    ->square()
                    ->size(48)
                    ->limit(1)
                    ->defaultImageUrl(fn () => asset('images/placeholder.svg')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary')
                    ->description(fn (Product $record): string => $record->sku)
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('stock_status')
                    ->label('Stock')
                    ->state(fn (Product $record): string => $record->stockStatusLabel())
                    ->color(fn (Product $record): string => $record->stockStatusColor())
                    ->icon(fn (Product $record): string => $record->stock_quantity === 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->sortable(['stock_quantity']),

                Tables\Columns\BadgeColumn::make('status')
                    ->badge()
                    ->state(fn (Product $record): string => $record->status->label())
                    ->color(fn (Product $record): string => match ($record->status) {
                        ProductStatus::ACTIVE => 'success',
                        ProductStatus::INACTIVE => 'danger',
                        ProductStatus::OUT_OF_STOCK => 'warning',
                    }),

                Tables\Columns\IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->searchable()
                    ->options(fn (): array => Category::query()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ProductStatus::ACTIVE->value => ProductStatus::ACTIVE->label(),
                        ProductStatus::INACTIVE->value => ProductStatus::INACTIVE->label(),
                        ProductStatus::OUT_OF_STOCK->value => ProductStatus::OUT_OF_STOCK->label(),
                    ]),

                Tables\Filters\TernaryFilter::make('low_stock')
                    ->label('Low Stock')
                    ->placeholder('Any stock level')
                    ->trueLabel('Low stock only')
                    ->falseLabel('Normal stock')
                    ->queries(
                        true: fn (Builder $query) => $query->lowStock(),
                        false: fn (Builder $query) => $query->where('stock_quantity', '>', 10),
                    ),

                Tables\Filters\TernaryFilter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->placeholder('Any stock level')
                    ->trueLabel('Out of stock only')
                    ->falseLabel('In stock')
                    ->queries(
                        true: fn (Builder $query) => $query->outOfStock(),
                        false: fn (Builder $query) => $query->where('stock_quantity', '>', 0),
                    ),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Featured')
                    ->placeholder('All products')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),

                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Min price')
                            ->numeric()
                            ->prefix('UGX'),
                        Forms\Components\TextInput::make('max')
                            ->label('Max price')
                            ->numeric()
                            ->prefix('UGX'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->priceBetween(
                            isset($data['min']) ? (float) $data['min'] : null,
                            isset($data['max']) ? (float) $data['max'] : null,
                        );
                    }),

                Tables\Filters\Filter::make('recently_updated')
                    ->label('Recently Updated')
                    ->query(fn (Builder $query): Builder => $query->recentlyUpdated(7))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->after(function (Product $record) {
                            AuditService::log(
                                auth()->user(),
                                'product.updated',
                                $record,
                                ['name' => $record->name, 'price' => $record->price, 'stock_quantity' => $record->stock_quantity]
                            );
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Product $record) {
                            AuditService::log(
                                auth()->user(),
                                'product.deleted',
                                $record,
                                ['name' => $record->name]
                            );
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                AuditService::log(
                                    auth()->user(),
                                    'product.deleted',
                                    $record,
                                    ['name' => $record->name]
                                );
                            }
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each->update(['status' => ProductStatus::ACTIVE->value]);
                            \Filament\Notifications\Notification::make()
                                ->title('Products activated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each->update(['status' => ProductStatus::INACTIVE->value]);
                            \Filament\Notifications\Notification::make()
                                ->title('Products deactivated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('feature')
                        ->label('Feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each->update(['featured' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Products marked as featured')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Unfeature')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each->update(['featured' => false]);
                            \Filament\Notifications\Notification::make()
                                ->title('Products removed from featured')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('assignCategory')
                        ->label('Assign Category')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->options(Category::query()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each->update(['category_id' => $data['category_id']]);
                            \Filament\Notifications\Notification::make()
                                ->title('Category assigned')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $csv = "Name,SKU,Category,Price,Stock,Status\n";
                            foreach ($records as $record) {
                                $csv .= '"' . $record->name . '","' . $record->sku . '","' . ($record->category?->name ?? '') . '",' . $record->price . ',' . $record->stock_quantity . ',"' . $record->status->label() . "\"\n";
                            }
                            \Illuminate\Support\Facades\Storage::disk('local')->put('exports/products.csv', $csv);
                            \Filament\Notifications\Notification::make()
                                ->title('Export ready')
                                ->body('The selected products have been exported to storage/app/exports/products.csv')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
