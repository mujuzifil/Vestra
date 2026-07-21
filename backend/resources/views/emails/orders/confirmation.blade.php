<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0a1628; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #22c55e; color: white; text-decoration: none; border-radius: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank you for your order!</h1>
    </div>
    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        <p>Your order <strong>{{ $order->invoice_number }}</strong> has been received and is being processed.</p>

        <h3>Order Summary</h3>
        <table>
            <thead>
                <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total: UGX {{ number_format($order->total_amount, 2) }}</strong></p>
        <p>Payment Method: {{ ucfirst($order->payment_method) }}</p>

        <p style="text-align: center; margin-top: 24px;">
            <a href="#" class="btn">View Order</a>
        </p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care</p>
        <p>Kampala, Uganda | vestradetergent@gmail.com</p>
    </div>
</body>
</html>
