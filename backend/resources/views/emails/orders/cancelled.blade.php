<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Cancelled</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #991b1b; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Order Has Been Cancelled</h1>
    </div>
    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        <p>Your order <strong>{{ $order->invoice_number }}</strong> has been cancelled.</p>
        <p>If you have already paid, a refund will be processed according to our refund policy.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care</p>
    </div>
</body>
</html>
