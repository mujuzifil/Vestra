<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reply to Your Message</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #22c55e; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border-radius: 0 0 8px 8px; }
        .message-box { background: white; padding: 15px; border-left: 4px solid #22c55e; margin: 15px 0; }
        .reply-box { background: #dcfce7; padding: 15px; border-left: 4px solid #166534; margin: 15px 0; }
        .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VESTRA</h1>
        <p>Reply to Your Message</p>
    </div>
    <div class="content">
        <p>Hello {{ $contactMessage->name }},</p>
        <p>Thank you for contacting us. Here is our response to your message:</p>

        <div class="message-box">
            <p><strong>Your Message:</strong></p>
            <p><strong>Subject:</strong> {{ $contactMessage->subject }}</p>
            <p>{{ $contactMessage->message }}</p>
        </div>

        <div class="reply-box">
            <p><strong>Our Reply:</strong></p>
            <p>{{ $contactMessage->reply }}</p>
        </div>

        <p>If you have any further questions, please feel free to contact us again.</p>
    </div>
    <div class="footer">
        <p>VESTRA — Professional Fabric Care Solutions</p>
        <p>Email: vestradetergent@gmail.com | Phone: +256 707 128 442</p>
    </div>
</body>
</html>
