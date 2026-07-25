<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .company { color: #0a1628; }
        .company h1 { margin: 0; font-size: 28px; color: #22c55e; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { margin: 0; font-size: 24px; color: #0a1628; }
        .section { margin-bottom: 24px; }
        .section h3 { font-size: 14px; text-transform: uppercase; color: #64748b; margin-bottom: 8px; border-bottom: 2px solid #22c55e; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #f1f5f9; padding: 10px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .totals { width: 300px; margin-left: auto; margin-top: 24px; }
        .totals td { border: none; padding: 6px 10px; }
        .totals .grand { font-size: 18px; font-weight: bold; color: #0a1628; border-top: 2px solid #0a1628; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 12px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-refunded { background: #f3f4f6; color: #374151; }
        .company-logo { max-height: 60px; margin-bottom: 8px; }
        .meta-grid { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        .meta-box { flex: 1; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            @if (!empty($company['logo']))
                <img src="{{ $company['logo'] }}" alt="{{ $company['name'] }}" class="company-logo">
            @endif
            <h1>{{ $company['name'] }}</h1>
            <p>Professional Fabric Care</p>
            <p>{{ $company['address'] }}</p>
            <p>{{ $company['email'] }}</p>
            <p>{{ $company['phone'] }}</p>
        </div>
        <div class="invoice-meta">
            <h2>INVOICE</h2>
            <p><strong>{{ $order->invoice_number }}</strong></p>
            <p>Date: {{ $order->created_at->format('F j, Y') }}</p>
            <p>Payment Status: <span class="status status-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></p>
            <p>Order Status: <span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></p>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-box section">
            <h3>Bill To</h3>
            <p><strong>{{ $order->user->name }}</strong></p>
            <p>{{ $order->user->email }}</p>
            <p>{{ $order->shipping_address['address_line'] ?? '' }}</p>
            <p>{{ $order->shipping_address['city'] ?? '' }}</p>
        </div>
        <div class="meta-box section">
            <h3>Ship To</h3>
            <p><strong>{{ $order->shipping_address['full_name'] ?? $order->user->name }}</strong></p>
            <p>{{ $order->shipping_address['phone'] ?? '' }}</p>
            <p>{{ $order->shipping_address['address_line'] ?? '' }}</p>
            <p>{{ $order->shipping_address['city'] ?? '' }}</p>
        </div>
    </div>

    <div class="section">
        <h3>Order Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_sku }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                    <td>UGX {{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td style="text-align: right;">UGX {{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Shipping</td>
            <td style="text-align: right;">UGX {{ number_format($order->shipping_cost, 2) }}</td>
        </tr>
        <tr>
            <td>VAT / Tax</td>
            <td style="text-align: right;">UGX {{ number_format($order->tax_amount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Grand Total</td>
            <td style="text-align: right;">UGX {{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Amount Paid</td>
            <td style="text-align: right;">UGX {{ number_format($order->amountPaid(), 2) }}</td>
        </tr>
        <tr>
            <td>Outstanding Balance</td>
            <td style="text-align: right;">UGX {{ number_format($order->outstandingBalance(), 2) }}</td>
        </tr>
    </table>

    <div class="section">
        <h3>Terms & Support</h3>
        <p>Payment is due according to the selected payment method. For questions about this invoice, contact us at {{ $company['email'] }} or {{ $company['phone'] }}.</p>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{ $company['name'] }} — Professional Fabric Care Solutions</p>
    </div>
</body>
</html>
