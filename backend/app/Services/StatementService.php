<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\CreditTransaction;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\PaymentUpload;
use Carbon\Carbon;

class StatementService
{
    public function generate(Distributor $distributor, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ? $from->copy()->startOfDay() : now()->copy()->subDays(30)->startOfDay();
        $to = $to ? $to->copy()->endOfDay() : now()->copy()->endOfDay();

        $rows = collect();

        $orders = $distributor->orders()
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', '!=', PaymentStatus::REFUNDED->value)
            ->orderBy('created_at')
            ->get();

        foreach ($orders as $order) {
            $rows->push([
                'date' => $order->created_at->toDateTimeString(),
                'type' => 'order',
                'reference' => $order->invoice_number,
                'description' => 'Order #' . $order->invoice_number,
                'debit' => (float) $order->total_amount,
                'credit' => 0.0,
                'balance_impact' => (float) $order->total_amount,
            ]);
        }

        $paymentUploads = $distributor->paymentUploads()
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['verified'])
            ->orderBy('created_at')
            ->get();

        foreach ($paymentUploads as $upload) {
            $rows->push([
                'date' => $upload->created_at->toDateTimeString(),
                'type' => 'payment_upload',
                'reference' => $upload->reference_number,
                'description' => 'Payment upload' . ($upload->reference_number ? " ({$upload->reference_number})" : ''),
                'debit' => 0.0,
                'credit' => (float) $upload->amount,
                'balance_impact' => -(float) $upload->amount,
            ]);
        }

        $creditAccount = $distributor->creditAccount;

        if ($creditAccount) {
            $creditTransactions = CreditTransaction::where('credit_account_id', $creditAccount->id)
                ->whereBetween('created_at', [$from, $to])
                ->whereIn('type', ['capture', 'payment'])
                ->orderBy('created_at')
                ->get();

            foreach ($creditTransactions as $transaction) {
                $isCapture = $transaction->type->value === 'capture';
                $amount = abs((float) $transaction->amount);

                $rows->push([
                    'date' => $transaction->created_at->toDateTimeString(),
                    'type' => 'credit_' . $transaction->type->value,
                    'reference' => $transaction->reference?->invoice_number ?? (string) $transaction->id,
                    'description' => $transaction->description ?? 'Credit transaction',
                    'debit' => $isCapture ? $amount : 0.0,
                    'credit' => $isCapture ? 0.0 : $amount,
                    'balance_impact' => $isCapture ? $amount : -$amount,
                ]);
            }
        }

        $rows = $rows->sortBy('date')->values();

        $openingBalance = $this->openingBalance($distributor, $from);
        $runningBalance = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $rows->transform(function (array $row) use (&$runningBalance, &$totalDebit, &$totalCredit): array {
            $runningBalance += $row['balance_impact'];
            $totalDebit += $row['debit'];
            $totalCredit += $row['credit'];

            return array_merge($row, [
                'running_balance' => round($runningBalance, 2),
            ]);
        });

        return [
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'currency' => 'UGX',
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'rows' => $rows,
        ];
    }

    private function openingBalance(Distributor $distributor, Carbon $from): float
    {
        $ordersTotal = (float) $distributor->orders()
            ->where('created_at', '<', $from)
            ->where('payment_status', '!=', PaymentStatus::REFUNDED->value)
            ->sum('total_amount');

        $paymentsTotal = (float) $distributor->paymentUploads()
            ->where('created_at', '<', $from)
            ->where('status', 'verified')
            ->sum('amount');

        $creditAccount = $distributor->creditAccount;
        $creditTotal = 0.0;

        if ($creditAccount) {
            $creditTotal = (float) CreditTransaction::where('credit_account_id', $creditAccount->id)
                ->where('created_at', '<', $from)
                ->whereIn('type', ['capture', 'payment'])
                ->sum('amount');
        }

        return $ordersTotal + $creditTotal - $paymentsTotal;
    }
}
