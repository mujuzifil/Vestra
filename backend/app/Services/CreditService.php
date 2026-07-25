<?php

namespace App\Services;

use App\Enums\CreditTransactionType;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function updateLimit(CreditAccount $account, float $newLimit, string $reason): CreditTransaction
    {
        return DB::transaction(function () use ($account, $newLimit, $reason) {
            $oldLimit = (float) $account->limit;

            $account->update([
                'limit' => max(0, $newLimit),
                'status' => $account->status ?: 'active',
            ]);

            $transaction = CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => CreditTransactionType::LIMIT_CHANGE,
                'amount' => 0,
                'balance_after' => $account->balance,
                'description' => "Credit limit changed from {$oldLimit} to {$account->limit}" . ($reason ? ": {$reason}" : ''),
                'created_by' => auth()->id(),
            ]);

            AuditService::log(auth()->user(), 'credit_account.limit_updated', $account, [
                'old_limit' => $oldLimit,
                'new_limit' => (float) $account->limit,
                'reason' => $reason,
            ]);

            return $transaction;
        });
    }

    public function addTransaction(
        CreditAccount $account,
        string $type,
        float $amount,
        string $description,
        ?array $meta = null
    ): CreditTransaction {
        $typeEnum = CreditTransactionType::tryFrom($type) ?? CreditTransactionType::ADJUSTMENT;

        return DB::transaction(function () use ($account, $typeEnum, $amount, $description, $meta) {
            $balanceChangingTypes = [
                CreditTransactionType::ADJUSTMENT,
                CreditTransactionType::PAYMENT,
            ];

            if (in_array($typeEnum, $balanceChangingTypes, true)) {
                $newBalance = (float) $account->balance + $amount;
                $account->update(['balance' => $newBalance]);
            }

            $transaction = CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => $typeEnum,
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_id' => $meta['reference_id'] ?? null,
                'reference_type' => $meta['reference_type'] ?? null,
                'description' => $description,
                'created_by' => $meta['created_by'] ?? auth()->id(),
            ]);

            AuditService::log(auth()->user(), 'credit_account.transaction_created', $account, [
                'transaction_id' => $transaction->id,
                'type' => $typeEnum->value,
                'amount' => $amount,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    public function availableCredit(CreditAccount $account): float
    {
        return $account->availableCredit();
    }
}
