@extends('emails.layout')

@section('title', 'New Crystal Movers enquiry')

@section('content')
    <p style="margin-top:0;"><strong>New Crystal Movers enquiry</strong> &mdash; {{ $quote->reference_number }}</p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="font-size:14px;width:100%;">
        <tr><td style="color:#6b7280;width:38%;">Name</td><td>{{ $quote->name }}</td></tr>
        <tr><td style="color:#6b7280;">Phone</td><td>{{ $quote->phone }}</td></tr>
        <tr><td style="color:#6b7280;">Email</td><td>{{ $quote->email ?? 'Not provided' }}</td></tr>
        <tr><td style="color:#6b7280;">Service</td><td>{{ $quote->service?->title ?? $quote->move_type?->label() ?? 'Not specified' }}@if ($quote->service?->category) ({{ $quote->service->category->name }})@endif</td></tr>
        @if ($quote->from_location || $quote->to_location)
            <tr><td style="color:#6b7280;">From</td><td>{{ $quote->from_location ?? '-' }}</td></tr>
            <tr><td style="color:#6b7280;">To</td><td>{{ $quote->to_location ?? '-' }}</td></tr>
        @endif
        @if ($quote->location)
            <tr><td style="color:#6b7280;">Location</td><td>{{ $quote->location }}</td></tr>
        @endif
        <tr><td style="color:#6b7280;">Preferred date</td><td>{{ $quote->preferred_date?->format('l, j F Y') ?? 'Not specified' }}{{ $quote->preferred_time ? ', '.$quote->preferred_time : '' }}</td></tr>
        @foreach (($quote->details ?? []) as $key => $value)
            <tr><td style="color:#6b7280;">{{ ucfirst(str_replace('_', ' ', $key)) }}</td><td>{{ $value }}</td></tr>
        @endforeach
        <tr><td style="color:#6b7280;">Attachments</td><td>{{ $quote->attachments->count() }}</td></tr>
        <tr><td style="color:#6b7280;vertical-align:top;">Message</td><td style="white-space:pre-line;">{{ $quote->message ?? '-' }}</td></tr>
    </table>

    <p>
        <a href="{{ url('/admin/quotes/'.$quote->id) }}" style="background:#0EA5E9;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;display:inline-block;">
            Open in admin
        </a>
    </p>
@endsection
