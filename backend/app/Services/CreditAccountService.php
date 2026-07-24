<?php

namespace App\Services;

use App\Enums\CreditTransactionType;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Distributor;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CreditAccountService
{
    public function forDistributor(Distributor $distributor): CreditAccount
    {
        return CreditAccount::firstOrCreate(
            ['distributor_id' => $distributor->id],
            ['limit' => 0, 'balance' => 0, 'authorized_amount' => 0, 'status' => 'pending']
        );
    }

    public function authorize(Distributor $distributor, float $amount, Order $order): bool
    {
        $account = $this->forDistributor($distributor);

        if ($account->status !== 'active') {
            return false;
        }

        if ($amount > $account->availableCredit()) {
            return false;
        }

        return DB::transaction(function () use ($account, $amount, $order) {
            $account->increment('authorized_amount', $amount);

            CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => CreditTransactionType::AUTHORIZATION,
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_id' => $order->id,
                'reference_type' => Order::class,
                'description' => "Authorized credit for order {$order->invoice_number}",
            ]);

            return true;
        });
    }

    public function capture(Distributor $distributor, float $amount, Order $order): bool
    {
        $account = $this->forDistributor($distributor);

        return DB::transaction(function () use ($account, $amount, $order) {
            $account->decrement('authorized_amount', $amount);
            $account->increment('balance', $amount);

            CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => CreditTransactionType::CAPTURE,
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_id' => $order->id,
                'reference_type' => Order::class,
                'description' => "Captured credit for order {$order->invoice_number}",
            ]);

            return true;
        });
    }

    public function release(Distributor $distributor, float $amount, Order $order): bool
    {
        $account = $this->forDistributor($distributor);

        return DB::transaction(function () use ($account, $amount, $order) {
            $account->decrement('authorized_amount', $amount);

            CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => CreditTransactionType::RELEASE,
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_id' => $order->id,
                'reference_type' => Order::class,
                'description' => "Released credit authorization for order {$order->invoice_number}",
            ]);

            return true;
        });
    }

    public function payment(Distributor $distributor, float $amount, ?string $reference = null, ?int $createdBy = null): bool
    {
        $account = $this->forDistributor($distributor);

        return DB::transaction(function () use ($account, $amount, $reference, $createdBy) {
            $account->decrement('balance', $amount);

            CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => CreditTransactionType::PAYMENT,
                'amount' => -$amount,
                'balance_after' => $account->balance,
                'reference_id' => null,
                'reference_type' => $reference ? 'payment_reference' : null,
                'description' => "Payment received" . ($reference ? " (Ref: {$reference})" : ''),
                'created_by' => $createdBy,
            ]);

            return true;
        });
    }

    public function setLimit(Distributor $distributor, float $limit, ?int $createdBy = null): CreditAccount
    {
        $account = $this->forDistributor($distributor);

        $oldLimit = $account->limit;
        $account->update([
            'limit' => $limit,
            'status' => 'active',
        ]);

        CreditTransaction::create([
            'credit_account_id' => $account->id,
            'type' => CreditTransactionType::LIMIT_CHANGE,
            'amount' => 0,
            'balance_after' => $account->balance,
            'reference_id' => null,
            'reference_type' => null,
            'description' => "Credit limit changed from {$oldLimit} to {$limit}",
            'created_by' => $createdBy,
        ]);

        return $account;
    }
}
