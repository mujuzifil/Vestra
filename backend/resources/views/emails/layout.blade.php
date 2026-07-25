<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('subject', 'VESTRA Notification')</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #1e3a5f; padding: 24px; text-align: center; }
        .header img { max-height: 48px; }
        .content { padding: 32px 24px; color: #333333; font-size: 16px; line-height: 1.6; }
        .footer { background-color: #f4f6f8; padding: 24px; text-align: center; font-size: 13px; color: #6b7280; }
        .button { display: inline-block; padding: 12px 24px; background-color: #1e3a5f; color: #ffffff; text-decoration: none; border-radius: 4px; }
        @media only screen and (max-width: 600px) {
            .content { padding: 24px 16px; }
        }
    </style>
</head>
<body>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div class="container">
                    <div class="header">
                        <a href="{{ config('app.frontend_url', config('app.url')) }}" style="color: #ffffff; text-decoration: none; font-size: 22px; font-weight: 700;">
                            VESTRA Detergents
                        </a>
                    </div>
                    <div class="content">
                        @yield('content')
                    </div>
                    <div class="footer">
                        <p>&copy; {{ date('Y') }} VESTRA Detergents. All rights reserved.</p>
                        <p>{{ config('app.frontend_url', config('app.url')) }}</p>
                        <p style="font-size: 12px; color: #9ca3af;">You are receiving this email because you have an account on the VESTRA platform.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
