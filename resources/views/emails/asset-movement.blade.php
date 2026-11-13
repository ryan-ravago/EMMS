<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

@php
    $isOut = $kind === 'out';
    $headerColor = $isOut ? '#d97706' : '#16a34a';
    $headline = ($isOut ? 'Time out' : 'Time in') . ': ' . $assetLabel;
@endphp

<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="width:600px;max-width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:{{ $headerColor }};padding:24px 32px;">
                            <p style="margin:0 0 4px;color:#ffffff;font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                                {{ $assetType }} {{ $isOut ? 'left the yard' : 'came back in' }}
                            </p>
                            <h1 style="margin:0;color:#ffffff;font-size:18px;font-weight:700;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;margin-bottom:20px;">
                                @foreach ($details as $label => $value)
                                    <tr>
                                        <td style="padding:10px 14px;font-size:13px;color:#6b7280;width:40%;border-bottom:1px solid #e5e7eb;">{{ $label }}</td>
                                        <td style="padding:10px 14px;font-size:13px;color:#111827;font-weight:600;border-bottom:1px solid #e5e7eb;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            @if ($note)
                                <p style="margin:0 0 20px;font-size:13px;color:#92400e;background-color:#fffbeb;border:1px solid #fde68a;padding:10px 14px;line-height:1.6;">{{ $note }}</p>
                            @endif

                            @if ($tripUrl)
                                <p style="margin:0 0 24px;">
                                    <a href="{{ $tripUrl }}"
                                        style="display:inline-block;background-color:#03449d;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:10px 18px;border-radius:6px;">View in EMS</a>
                                </p>
                            @endif

                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                                You receive this because your role is set up to be told about equipment and service vehicle time outs and time ins.
                                @if ($profileUrl)
                                    You can turn these emails off on your <a href="{{ $profileUrl }}" style="color:#6b7280;">My Profile</a> page.
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
