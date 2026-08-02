<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'quotes' => $this['quotes'],
            'support_enquiries' => $this['support_enquiries'],
            'documents' => $this['documents'],
            'saved_products' => $this['saved_products'],
            'unread_notifications' => $this['unread_notifications'],
            'distributor_status' => $this['distributor_status'],
            'recent_quotes' => CustomerQuoteResource::collection($this['recent_quotes']),
            'recent_documents' => CustomerDocumentResource::collection($this['recent_documents']),
        ];
    }
}
