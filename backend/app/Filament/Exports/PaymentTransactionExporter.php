<?php

namespace App\Filament\Exports;

use App\Models\PaymentTransaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PaymentTransactionExporter extends Exporter
{
    protected static ?string $model = PaymentTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order.invoice_number')->label('Invoice'),
            ExportColumn::make('transaction_reference')->label('Transaction ID'),
            ExportColumn::make('provider_reference')->label('Provider Reference'),
            ExportColumn::make('amount'),
            ExportColumn::make('currency'),
            ExportColumn::make('status'),
            ExportColumn::make('payment_method')->label('Payment Method'),
            ExportColumn::make('paid_at'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
