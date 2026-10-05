<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" class="email-container"
                    style="width:600px;max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td class="email-header" style="background-color:#4f46e5;padding:30px 40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">Welcome to {{ config('app.name') }}</h1>
                            <p style="margin:6px 0 0;color:#c7d2fe;font-size:13px;">Your access has been set up</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="email-body" style="padding:32px 40px;">
                            <p style="margin:0 0 20px;font-size:15px;color:#374151;">
                                Hello <strong>{{ $user->user_fname }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                An administrator gave you access to {{ config('app.name') }}. Sign in with your company
                                account: use this email address (<strong>{{ $user->user_email }}</strong>) and your usual
                                password, or choose <strong>Sign in with Google</strong>.
                            </p>

                            <p style="margin:0 0 28px;text-align:center;">
                                <a href="{{ $loginUrl }}"
                                    style="display:inline-block;background-color:#4f46e5;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;padding:12px 28px;border-radius:6px;">
                                    Open {{ config('app.name') }}
                                </a>
                            </p>

                            <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                                Forgot your password? Use <strong>Forgot password</strong> on the login page, and you can
                                change it anytime from <strong>Change Password</strong> in your account menu.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer" style="background-color:#f9fafb;padding:20px 40px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                                If you were not expecting this email, you can ignore it. This is an automated message,
                                please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
