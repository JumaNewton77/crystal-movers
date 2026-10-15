@extends('emails.layout')

@section('title', 'Your quote request has been received')

@section('content')
    <p style="margin-top:0;">Hello {{ $quote->name }},</p>
    <p><strong>Your Crystal Movers request has been received.</strong> Our team will review your requirements and contact you.</p>

    <p style="background:#e0f2fe;border-radius:6px;padding:12px 16px;font-size:16px;">
        Your reference: <strong>{{ $quote->reference_number }}</strong>
    </p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="font-size:14px;width:100%;">
        <tr><td style="color:#6b7280;width:38%;">Service</td><td>{{ $quote->service?->title ?? $quote->move_type?->label() ?? 'Not specified' }}</td></tr>
        @if ($quote->from_location || $quote->to_location)
            <tr><td style="color:#6b7280;">Moving from</td><td>{{ $quote->from_location ?? '-' }}</td></tr>
            <tr><td style="color:#6b7280;">Moving to</td><td>{{ $quote->to_location ?? '-' }}</td></tr>
        @endif
        @if ($quote->location)
            <tr><td style="color:#6b7280;">Location</td><td>{{ $quote->location }}</td></tr>
        @endif
        <tr><td style="color:#6b7280;">Preferred date</td><td>{{ $quote->preferred_date?->format('l, j F Y') ?? 'Not specified' }}{{ $quote->preferred_time ? ', '.$quote->preferred_time : '' }}</td></tr>
        <tr><td style="color:#6b7280;">Your phone</td><td>{{ $quote->phone }}</td></tr>
        <tr><td style="color:#6b7280;">Your email</td><td>{{ $quote->email }}</td></tr>
        @if ($quote->attachments->count())
            <tr><td style="color:#6b7280;">Files received</td><td>{{ $quote->attachments->count() }}</td></tr>
        @endif
    </table>

    <p>Please quote the reference above if you contact us about this request.</p>
    <p style="margin-bottom:0;">Thank you,<br>{{ $company['name'] }}</p>
@endsection
