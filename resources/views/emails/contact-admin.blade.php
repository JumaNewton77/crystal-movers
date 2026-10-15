@extends('emails.layout')

@section('title', 'New contact message')

@section('content')
    <p style="margin-top:0;"><strong>New Crystal Movers contact message</strong></p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="font-size:14px;width:100%;">
        <tr><td style="color:#6b7280;width:38%;">Name</td><td>{{ $contact->name }}</td></tr>
        <tr><td style="color:#6b7280;">Phone</td><td>{{ $contact->phone ?? 'Not provided' }}</td></tr>
        <tr><td style="color:#6b7280;">Email</td><td>{{ $contact->email }}</td></tr>
        <tr><td style="color:#6b7280;">Subject</td><td>{{ $contact->subject }}</td></tr>
        <tr><td style="color:#6b7280;vertical-align:top;">Message</td><td style="white-space:pre-line;">{{ $contact->message }}</td></tr>
    </table>

    <p>
        <a href="{{ url('/admin/messages') }}" style="background:#0EA5E9;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;display:inline-block;">
            Open inbox
        </a>
    </p>
@endsection
