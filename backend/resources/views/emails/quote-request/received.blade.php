@extends('emails.layout')

@section('content')
<h1 style="color: #0d3b66; font-size: 24px; margin-bottom: 20px;">Thank you for your enquiry, {{ $quote->full_name }}</h1>

<p>We have received your quotation request and our sales team will contact you within <strong>24–48 business hours</strong>.</p>

<div style="background: #f5f7fa; border-radius: 8px; padding: 16px; margin: 24px 0;">
    <p style="margin: 0 0 8px;"><strong>Reference:</strong> {{ $quote->reference_number }}</p>
    <p style="margin: 0 0 8px;"><strong>Company:</strong> {{ $quote->company_name }}</p>
    <p style="margin: 0 0 8px;"><strong>Email:</strong> {{ $quote->email }}</p>
    <p style="margin: 0;"><strong>Phone:</strong> {{ $quote->phone }}</p>
</div>

@if ($items->isNotEmpty())
<h2 style="color: #0d3b66; font-size: 18px; margin-bottom: 12px;">Products Requested</h2>
<ul style="padding-left: 20px; margin-bottom: 24px;">
    @foreach ($items as $item)
    <li>
        <strong>{{ $item->quantity }} x {{ $item->product_name }}</strong>
        @if ($item->package_size)
        <span style="color: #6b7280;">({{ $item->package_size }})</span>
        @endif
    </li>
    @endforeach
</ul>
@endif

@if ($quote->delivery_location)
<p><strong>Delivery Location:</strong> {{ $quote->delivery_location }}</p>
@endif

@if ($quote->preferred_delivery_date)
<p><strong>Preferred Delivery Date:</strong> {{ $quote->preferred_delivery_date->format('F j, Y') }}</p>
@endif

<p style="margin-top: 24px;">If you have any questions, please reply to this email or call our sales team.</p>

<p style="margin-top: 24px;">Best regards,<br><strong>The VESTRA Team</strong></p>
@endsection
