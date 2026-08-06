<?php

namespace App\Filament\Resources;

use App\Enums\WorkflowStatus;
use App\Filament\Resources\AutomatedWorkflowResource\Pages;
use App\Models\AutomatedWorkflow;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AutomatedWorkflowResource extends Resource
{
    protected static ?string $model = AutomatedWorkflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Workflows';

    protected static ?string $modelLabel = 'Automated Workflow';

    protected static ?string $pluralModelLabel = 'Automated Workflows';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationItems(): array
    {
        return [];
    }

    public static function getCommonEvents(): array
    {
        return [
            'order.created' => 'Order Created',
            'order.paid' => 'Order Paid',
            'order.shipped' => 'Order Shipped',
            'order.delivered' => 'Order Delivered',
            'order.cancelled' => 'Order Cancelled',
            'customer.created' => 'Customer Created',
            'customer.updated' => 'Customer Updated',
            'customer.tag_assigned' => 'Customer Tag Assigned',
            'payment.received' => 'Payment Received',
            'payment.failed' => 'Payment Failed',
            'product.low_stock' => 'Product Low Stock',
            'product.out_of_stock' => 'Product Out of Stock',
        ];
    }

    public static function getActions(): array
    {
        return [
            'notification' => 'Send Notification',
            'email' => 'Send Email',
            'status_change' => 'Change Status',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Workflow Details')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('event')
                            ->options(static::getCommonEvents())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(collect(WorkflowStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]))
                            ->default(WorkflowStatus::DRAFT->value)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Conditions')
                    ->icon('heroicon-o-funnel')
                    ->description('Key-value pairs that must all match the event payload for this workflow to run.')
                    ->schema([
                        Forms\Components\Repeater::make('conditions')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->required()
                                    ->placeholder('e.g. order.total_amount')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('operator')
                                    ->options([
                                        'equals' => 'Equals',
                                        'not_equals' => 'Not equals',
                                        'greater_than' => 'Greater than',
                                        'less_than' => 'Less than',
                                        'contains' => 'Contains',
                                        'exists' => 'Exists',
                                    ])
                                    ->default('equals')
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('value')
                                    ->placeholder('Value to match')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add condition')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['key'] ? "{$state['key']} {$state['operator']} {$state['value']}" : null),
                    ]),

                Forms\Components\Section::make('Action')
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Forms\Components\Select::make('action')
                            ->options(static::getActions())
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Repeater::make('action_config')
                            ->label('Action Configuration')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->required()
                                    ->placeholder('e.g. subject, template, status')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('value')
                                    ->placeholder('Configuration value')
                                    ->columnSpan(2),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add configuration item')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['key'] ? "{$state['key']}: {$state['value']}" : null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getActions()[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'notification' => 'info',
                        'email' => 'success',
                        'status_change' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (WorkflowStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('run_count')
                    ->label('Runs')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('last_run_at')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options(static::getCommonEvents())
                    ->multiple()
                    ->native(false),

                Tables\Filters\SelectFilter::make('action')
                    ->options(static::getActions())
                    ->multiple()
                    ->native(false),

                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(WorkflowStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (AutomatedWorkflow $record) {
                        AuditService::log(
                            auth()->user(),
                            'workflow.updated',
                            $record,
                            ['name' => $record->name, 'event' => $record->event, 'status' => $record->status->value]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (AutomatedWorkflow $record) {
                        AuditService::log(
                            auth()->user(),
                            'workflow.deleted',
                            $record,
                            ['name' => $record->name, 'event' => $record->event]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('creator');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomatedWorkflows::route('/'),
            'edit' => Pages\EditAutomatedWorkflow::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
