<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #22c55e; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Received</h1>
    </div>
    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        <p>We've received your payment for order <strong>{{ $order->invoice_number }}</strong>.</p>
        <p><strong>Amount Paid:</strong> UGX {{ number_format($order->total_amount, 2) }}</p>
        <p>Your order is now being processed and will be shipped soon.</p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care</p>
    </div>
</body>
</html>
