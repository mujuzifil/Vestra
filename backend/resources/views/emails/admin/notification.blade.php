<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0a1628; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border-radius: 0 0 8px 8px; }
        .detail { background: white; padding: 10px 15px; margin: 8px 0; border-radius: 6px; }
        .detail strong { color: #0a1628; }
        .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VESTRA Admin</h1>
        <p>{{ $subjectLine }}</p>
    </div>
    <div class="content">
        <p>{{ $content }}</p>

        @if(count($data) > 0)
            <h3>Details:</h3>
            @foreach($data as $key => $value)
                <div class="detail">
                    <strong>{{ $key }}:</strong> {{ $value }}
                </div>
            @endforeach
        @endif
    </div>
    <div class="footer">
        <p>VESTRA Administration Panel</p>
    </div>
</body>
</html>
