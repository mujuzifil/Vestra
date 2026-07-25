<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => $this['period'],
            'total_orders' => $this['total_orders'],
            'total_revenue' => $this['total_revenue'],
            'total_quotes' => $this['total_quotes'],
            'pending_quotes' => $this['pending_quotes'],
            'average_order_value' => $this['average_order_value'],
            'month_over_month_growth' => $this['month_over_month_growth'],
            'orders_by_status' => $this['orders_by_status'],
            'revenue_by_month' => $this['revenue_by_month'],
            'orders_count' => $this['orders_count'],
            'orders_total' => $this['orders_total'],
            'quotations_count' => $this['quotations_count'],
            'accepted_quotations_count' => $this['accepted_quotations_count'],
            'pending_payments_total' => $this['pending_payments_total'],
            'credit_utilization_percentage' => $this['credit_utilization_percentage'],
            'top_products' => $this['top_products'],
        ];
    }
}
