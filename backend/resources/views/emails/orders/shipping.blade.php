<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Shipped</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d3b66; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Order Has Shipped!</h1>
    </div>
    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        <p>Your order <strong>{{ $order->invoice_number }}</strong> has been shipped.</p>
        @if ($order->courier)
        <p><strong>Courier:</strong> {{ $order->courier }}</p>
        @endif
        @if ($order->tracking_number)
        <p><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
        @endif
        <p>Thank you for shopping with VESTRA.</p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care</p>
    </div>
</body>
</html>
