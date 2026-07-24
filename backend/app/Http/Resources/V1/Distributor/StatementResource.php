<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $transactions = collect($this['rows'])->map(fn (array $row) => [
            'date' => $row['date'],
            'description' => $row['description'],
            'invoice_number' => $row['reference'],
            'debit' => number_format((float) $row['debit'], 2),
            'credit' => number_format((float) $row['credit'], 2),
            'balance' => number_format((float) $row['running_balance'], 2),
        ])->values()->all();

        return [
            'opening_balance' => number_format((float) $this['opening_balance'], 2),
            'closing_balance' => number_format((float) $this['closing_balance'], 2),
            'period_start' => $this['from'],
            'period_end' => $this['to'],
            'transactions' => $transactions,
        ];
    }
}
