@extends('emails.layout')

@section('content')
    <h1 style="font-size: 24px; font-weight: bold; color: #0d3b66; margin-bottom: 16px;">
        Thank you for contacting VESTRA®
    </h1>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 16px;">
        Hi {{ $contactMessage->name }},
    </p>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 16px;">
        We have received your message and a member of our team will respond within 24–48 business hours.
    </p>

    <table style="width: 100%; background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Subject</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->subject }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Enquiry Type</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->enquiryTypeLabel() }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Message</td>
        </tr>
        <tr>
            <td style="color: #475569; line-height: 1.6;">{{ $contactMessage->message }}</td>
        </tr>
    </table>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 8px;">
        If your matter is urgent, please call or WhatsApp us on +256 707 128 442.
    </p>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 0;">
        Best regards,<br>
        The VESTRA® Team
    </p>
@stop
