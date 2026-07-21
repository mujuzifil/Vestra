<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\AuditService;
use App\Services\OrderStatusService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'E-Commerce';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fulfilment')
                    ->icon('heroicon-o-truck')
                    ->description('Shipping and delivery tracking information.')
                    ->schema([
                        Forms\Components\TextInput::make('courier')
                            ->placeholder('e.g. DHL, FedEx, SafeBoda'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->placeholder('Tracking number'),
                        Forms\Components\DateTimePicker::make('dispatched_at')
                            ->label('Dispatched At'),
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Delivered At'),
                    ])->columns(2),

                Forms\Components\Section::make('Internal Notes')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->description('Staff-only notes about this order.')
                    ->schema([
                        Forms\Components\Textarea::make('internal_notes')
                            ->rows(3)
                            ->placeholder('Internal notes for staff')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $record): string => $record->user?->email ?? ''),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->badge()
                    ->color('gray')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->formatStateUsing(fn (string $state): string => PaymentStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => PaymentStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => OrderStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => OrderStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('invoice')
                            ->label('Invoice Number')
                            ->placeholder('Search invoice...'),
                        Forms\Components\TextInput::make('customer')
                            ->label('Customer Name')
                            ->placeholder('Search customer...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['invoice'] ?? null, fn (Builder $q, string $invoice) => $q->forInvoice($invoice))
                            ->when($data['customer'] ?? null, fn (Builder $q, string $name) => $q->forCustomerName($name));
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->multiple()
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Tables\Filters\Filter::make('value_range')
                    ->label('Order Value')
                    ->form([
                        Forms\Components\TextInput::make('min')->label('Min')->numeric()->prefix('UGX'),
                        Forms\Components\TextInput::make('max')->label('Max')->numeric()->prefix('UGX'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'] ?? null, fn (Builder $q, float $min) => $q->where('total_amount', '>=', $min))
                            ->when($data['max'] ?? null, fn (Builder $q, float $max) => $q->where('total_amount', '<=', $max));
                    }),

                Tables\Filters\Filter::make('recently_updated')
                    ->label('Recently Updated')
                    ->query(fn (Builder $query): Builder => $query->recentlyUpdated(7))
                    ->toggle(),

                Tables\Filters\Filter::make('high_value')
                    ->label('High Value')
                    ->query(fn (Builder $query): Builder => $query->highValue(200000))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    self::getMarkAsPaidAction(),
                    self::getMarkAsProcessingAction(),
                    self::getMarkAsPackedAction(),
                    self::getMarkAsShippedAction(),
                    self::getMarkAsDeliveredAction(),
                    self::getMarkAsCancelledAction(),
                    self::getMarkAsRefundedAction(),
                ])->label('Update Status'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(OrderExporter::class),

                    Tables\Actions\BulkAction::make('markProcessing')
                        ->label('Mark Processing')
                        ->icon('heroicon-o-cog')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkTransition($records, OrderStatus::PROCESSING, 'Marked as processing by admin', 'order.marked_processing', 'Orders marked as processing')),

                    Tables\Actions\BulkAction::make('markShipped')
                        ->label('Mark Shipped')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkTransition($records, OrderStatus::SHIPPED, 'Marked as shipped by admin', 'order.marked_shipped', 'Orders marked as shipped')),

                    Tables\Actions\BulkAction::make('markDelivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkTransition($records, OrderStatus::DELIVERED, 'Marked as delivered by admin', 'order.marked_delivered', 'Orders marked as delivered')),

                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkTransition($records, OrderStatus::CANCELLED, 'Cancelled by admin', 'order.cancelled', 'Orders cancelled')),

                    Tables\Actions\BulkAction::make('printInvoices')
                        ->label('Print Invoices')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Print Invoices')
                        ->modalDescription('Invoice printing integration will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Print integration is planned')->info()->send();
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(OrderExporter::class),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Order $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->persistFiltersInSession();
    }

    private static function bulkTransition(Collection $records, OrderStatus $toStatus, string $notes, string $auditAction, string $successTitle): void
    {
        $service = app(OrderStatusService::class);
        $successCount = 0;
        $skippedCount = 0;

        foreach ($records as $record) {
            if ($service->canTransition($record, $toStatus)) {
                $service->transition($record, $toStatus, $notes, auth()->id());
                AuditService::log(auth()->user(), $auditAction, $record, ['invoice_number' => $record->invoice_number]);
                $successCount++;
            } else {
                $skippedCount++;
            }
        }

        $message = "{$successCount} order(s) updated.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} order(s) skipped due to invalid status.";
        }

        Notification::make()
            ->title($successTitle)
            ->body($message)
            ->success()
            ->send();
    }

    private static function getMarkAsPaidAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsPaid')
            ->label('Mark Paid')
            ->icon('heroicon-o-currency-dollar')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Mark order as Paid')
            ->modalDescription('This will confirm payment has been received.')
            ->visible(fn (Model $record): bool => $record->status === OrderStatus::PENDING->value)
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::PAID, 'Marked as paid by admin', auth()->id());

                if ($success) {
                    $record->update(['payment_status' => PaymentStatus::PAID->value]);
                    AuditService::log(auth()->user(), 'order.marked_paid', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order marked as paid')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsProcessingAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsProcessing')
            ->label('Mark Processing')
            ->icon('heroicon-o-cog')
            ->color('primary')
            ->requiresConfirmation()
            ->visible(fn (Model $record): bool => $record->status === OrderStatus::PAID->value)
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::PROCESSING, 'Marked as processing by admin', auth()->id());

                if ($success) {
                    AuditService::log(auth()->user(), 'order.marked_processing', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order marked as processing')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsPackedAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsPacked')
            ->label('Mark Packed')
            ->icon('heroicon-o-archive-box')
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Model $record): bool => $record->status === OrderStatus::PROCESSING->value)
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::PACKED, 'Marked as packed by admin', auth()->id());

                if ($success) {
                    AuditService::log(auth()->user(), 'order.marked_packed', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order marked as packed')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsShippedAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsShipped')
            ->label('Mark Shipped')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Mark order as Shipped')
            ->modalDescription('This will record the dispatch date and notify the customer.')
            ->visible(fn (Model $record): bool => $record->status === OrderStatus::PACKED->value)
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::SHIPPED, 'Marked as shipped by admin', auth()->id());

                if ($success) {
                    AuditService::log(auth()->user(), 'order.marked_shipped', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order marked as shipped')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsDeliveredAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsDelivered')
            ->label('Mark Delivered')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Model $record): bool => $record->status === OrderStatus::SHIPPED->value)
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::DELIVERED, 'Marked as delivered by admin', auth()->id());

                if ($success) {
                    AuditService::log(auth()->user(), 'order.marked_delivered', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order marked as delivered')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsCancelledAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsCancelled')
            ->label('Cancel')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancel Order')
            ->modalDescription('This will cancel the order and restore stock.')
            ->visible(fn (Model $record): bool => in_array($record->status, [
                OrderStatus::PENDING->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::PACKED->value,
            ], true))
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::CANCELLED, 'Cancelled by admin', auth()->id());

                if ($success) {
                    AuditService::log(auth()->user(), 'order.cancelled', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order cancelled')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    private static function getMarkAsRefundedAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsRefunded')
            ->label('Refund')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Refund Order')
            ->modalDescription('This will mark the order as refunded and restore stock.')
            ->visible(fn (Model $record): bool => in_array($record->status, [
                OrderStatus::PAID->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::DELIVERED->value,
            ], true))
            ->action(function (Model $record): void {
                $service = app(OrderStatusService::class);
                $success = $service->transition($record, OrderStatus::REFUNDED, 'Refunded by admin', auth()->id());

                if ($success) {
                    $record->update(['payment_status' => PaymentStatus::REFUNDED->value]);
                    AuditService::log(auth()->user(), 'order.refunded', $record, ['invoice_number' => $record->invoice_number]);
                    Notification::make()->title('Order refunded')->success()->send();
                } else {
                    Notification::make()->title('Invalid status transition')->danger()->send();
                }
            });
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user'])
            ->withCount('items');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
