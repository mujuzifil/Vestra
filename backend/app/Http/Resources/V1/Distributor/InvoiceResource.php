<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'amount' => number_format((float) $this->total_amount, 2),
            'amount_paid' => number_format($this->amountPaid(), 2),
            'balance_due' => number_format($this->outstandingBalance(), 2),
            'due_date' => null,
            'paid_at' => $this->payment_status === 'paid' ? $this->updated_at : null,
            'subtotal' => number_format((float) $this->subtotal, 2),
            'tax_amount' => number_format((float) $this->tax_amount, 2),
            'total_amount' => number_format((float) $this->total_amount, 2),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
