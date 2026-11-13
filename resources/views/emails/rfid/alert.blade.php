<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

@php
    $headerColor = $kind === 'recovered' ? '#16a34a' : ($kind === 'token_expiring' ? '#d97706' : '#dc2626');
@endphp

<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="width:600px;max-width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:{{ $headerColor }};padding:24px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:18px;font-weight:700;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.6;">{{ $explanation }}</p>

                            @if ($details !== [])
                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="background-color:#f9fafb;border:1px solid #e5e7eb;margin-bottom:20px;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:10px 14px;font-size:13px;color:#6b7280;width:40%;border-bottom:1px solid #e5e7eb;">{{ $label }}</td>
                                            <td style="padding:10px 14px;font-size:13px;color:#111827;font-weight:600;border-bottom:1px solid #e5e7eb;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if ($steps !== [])
                                <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#111827;">What to check</p>
                                <ol style="margin:0 0 20px;padding-left:20px;font-size:13px;color:#374151;line-height:1.7;">
                                    @foreach ($steps as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            @endif

                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                Sent by EMS (rfid:check-health). Alerts are repeated while the problem lasts and a message is sent when it is fixed.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
