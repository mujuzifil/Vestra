<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Refunded</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #374151; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Refund Processed</h1>
    </div>
    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        <p>Your order <strong>{{ $order->invoice_number }}</strong> has been refunded.</p>
        <p>The refund amount of <strong>UGX {{ number_format($order->total_amount, 2) }}</strong> will be returned to your original payment method within 5–10 business days.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care</p>
    </div>
</body>
</html>
