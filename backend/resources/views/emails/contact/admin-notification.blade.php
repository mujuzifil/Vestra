@extends('emails.layout')

@section('content')
    <h1 style="font-size: 24px; font-weight: bold; color: #0d3b66; margin-bottom: 16px;">
        New Contact Message
    </h1>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 16px;">
        A new message has been submitted through the VESTRA® website.
    </p>

    <table style="width: 100%; background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Name</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->name }}</td>
        </tr>
        @if ($contactMessage->company)
            <tr>
                <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Company</td>
            </tr>
            <tr>
                <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->company }}</td>
            </tr>
        @endif
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Email</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->email }}</td>
        </tr>
        @if ($contactMessage->phone)
            <tr>
                <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Phone</td>
            </tr>
            <tr>
                <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->phone }}</td>
            </tr>
        @endif
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Enquiry Type</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->enquiryTypeLabel() }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Subject</td>
        </tr>
        <tr>
            <td style="color: #0d3b66; font-weight: 600; padding-bottom: 12px;">{{ $contactMessage->subject }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 4px;">Message</td>
        </tr>
        <tr>
            <td style="color: #475569; line-height: 1.6;">{{ $contactMessage->message }}</td>
        </tr>
    </table>

    <p style="color: #475569; line-height: 1.6; margin-bottom: 0;">
        Manage this message in the VESTRA® admin panel.
    </p>
@stop
