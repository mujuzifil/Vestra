<?php

namespace App\Models;

use App\Enums\CreditTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_account_id',
        'type',
        'amount',
        'balance_after',
        'reference_id',
        'reference_type',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(CreditAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
