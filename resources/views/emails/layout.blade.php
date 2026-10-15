<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background:#111827;padding:20px 24px;color:#ffffff;font-size:20px;font-weight:bold;">
                        {{ $company['name'] }} <span style="color:#0EA5E9;">&bull;</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;font-size:15px;line-height:1.55;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px;background:#f9fafb;font-size:12px;color:#6b7280;">
                        {{ $company['name'] }}
                        @if (! empty($company['phone'])) &middot; {{ $company['phone'] }} @endif
                        @if (! empty($company['email'])) &middot; {{ $company['email'] }} @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
