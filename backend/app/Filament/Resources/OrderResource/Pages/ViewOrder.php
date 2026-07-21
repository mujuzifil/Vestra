<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\AuditService;
use App\Services\OrderStatusService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.view-order';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->url(fn (Order $record): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),

            ...$this->getStatusActions(),
        ];
    }

    private function getStatusActions(): array
    {
        $order = $this->getRecord();
        $service = app(OrderStatusService::class);

        $actions = [];

        $transitions = [
            OrderStatus::PAID->value => [
                'toStatus' => OrderStatus::PAID,
                'label' => 'Mark Paid',
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'success',
                'modalHeading' => 'Mark order as Paid',
                'modalDescription' => 'This will confirm payment has been received.',
                'auditAction' => 'order.marked_paid',
                'successTitle' => 'Order marked as paid',
                'updatePayment' => PaymentStatus::PAID->value,
            ],
            OrderStatus::PROCESSING->value => [
                'toStatus' => OrderStatus::PROCESSING,
                'label' => 'Mark Processing',
                'icon' => 'heroicon-o-cog',
                'color' => 'primary',
                'modalHeading' => 'Mark order as Processing',
                'modalDescription' => 'This will start processing the order.',
                'auditAction' => 'order.marked_processing',
                'successTitle' => 'Order marked as processing',
            ],
            OrderStatus::PACKED->value => [
                'toStatus' => OrderStatus::PACKED,
                'label' => 'Mark Packed',
                'icon' => 'heroicon-o-archive-box',
                'color' => 'info',
                'modalHeading' => 'Mark order as Packed',
                'modalDescription' => 'This will mark the order as packed and ready for shipment.',
                'auditAction' => 'order.marked_packed',
                'successTitle' => 'Order marked as packed',
            ],
            OrderStatus::SHIPPED->value => [
                'toStatus' => OrderStatus::SHIPPED,
                'label' => 'Mark Shipped',
                'icon' => 'heroicon-o-truck',
                'color' => 'info',
                'modalHeading' => 'Mark order as Shipped',
                'modalDescription' => 'This will record the dispatch date and notify the customer.',
                'auditAction' => 'order.marked_shipped',
                'successTitle' => 'Order marked as shipped',
            ],
            OrderStatus::DELIVERED->value => [
                'toStatus' => OrderStatus::DELIVERED,
                'label' => 'Mark Delivered',
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
                'modalHeading' => 'Mark order as Delivered',
                'modalDescription' => 'This will mark the order as delivered.',
                'auditAction' => 'order.marked_delivered',
                'successTitle' => 'Order marked as delivered',
            ],
            OrderStatus::CANCELLED->value => [
                'toStatus' => OrderStatus::CANCELLED,
                'label' => 'Cancel Order',
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'modalHeading' => 'Cancel Order',
                'modalDescription' => 'This will cancel the order and restore stock.',
                'auditAction' => 'order.cancelled',
                'successTitle' => 'Order cancelled',
            ],
            OrderStatus::REFUNDED->value => [
                'toStatus' => OrderStatus::REFUNDED,
                'label' => 'Refund Order',
                'icon' => 'heroicon-o-arrow-uturn-left',
                'color' => 'gray',
                'modalHeading' => 'Refund Order',
                'modalDescription' => 'This will mark the order as refunded and restore stock.',
                'auditAction' => 'order.refunded',
                'successTitle' => 'Order refunded',
                'updatePayment' => PaymentStatus::REFUNDED->value,
            ],
        ];

        foreach ($transitions as $statusValue => $config) {
            $toStatus = $config['toStatus'];

            if (! $service->canTransition($order, $toStatus)) {
                continue;
            }

            $actions[] = Actions\Action::make('mark' . ucfirst($statusValue))
                ->label($config['label'])
                ->icon($config['icon'])
                ->color($config['color'])
                ->requiresConfirmation()
                ->modalHeading($config['modalHeading'])
                ->modalDescription($config['modalDescription'])
                ->action(function () use ($order, $toStatus, $service, $config): void {
                    $success = $service->transition($order, $toStatus, $config['label'] . ' by admin', auth()->id());

                    if ($success) {
                        if (isset($config['updatePayment'])) {
                            $order->update(['payment_status' => $config['updatePayment']]);
                        }
                        AuditService::log(auth()->user(), $config['auditAction'], $order, ['invoice_number' => $order->invoice_number]);
                        Notification::make()->title($config['successTitle'])->success()->send();
                    } else {
                        Notification::make()->title('Invalid status transition')->danger()->send();
                    }
                });
        }

        return $actions;
    }
}
