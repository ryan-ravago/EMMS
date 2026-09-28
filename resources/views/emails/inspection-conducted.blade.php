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

        /* Default / Desktop View: Hide 1-column mobile table */
        .mobile-table {
            display: none !important;
            max-height: 0px !important;
            overflow: hidden !important;
            mso-hide: all;
        }

        .desktop-table {
            display: table !important;
        }

        /* Mobile View (<= 600px): Hide 2-column desktop table and show 1-column mobile table */
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

            .desktop-table {
                display: none !important;
            }

            .mobile-table {
                display: table !important;
                max-height: none !important;
                overflow: visible !important;
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
                        <td class="email-header" style="background-color:{{ $recipientType === 'manager' && $hasFailedItems ? '#d97706' : '#16a34a' }};padding:30px 40px;">
                            @if ($recipientType === 'manager')
                                @if ($hasFailedItems)
                                    <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">⚠️ Inspection
                                        Requires Review</h1>
                                    <p style="margin:6px 0 0;color:#fde68a;font-size:13px;">An inspection has been
                                        submitted with failed items that require your attention.</p>
                                @else
                                    <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">✅ Inspection
                                        Complete – No Issues Found</h1>
                                    <p style="margin:6px 0 0;color:#bbf7d0;font-size:13px;">An inspection has been
                                        submitted with no failed items.</p>
                                @endif
                            @else
                                <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">✅ Inspection
                                    Submitted</h1>
                                <p style="margin:6px 0 0;color:#bbf7d0;font-size:13px;">Your inspection has been
                                    recorded successfully.</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="email-body" style="padding:32px 40px;">
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                @if ($recipientType === 'manager')
                                    @if ($hasFailedItems)
                                        An inspection has been submitted with <strong
                                            style="color:#111827;">{{ $failedItems->count() }} failed item(s)</strong>
                                        that require your review and action.
                                    @else
                                        An inspection has been submitted with no failed items. No further action is
                                        required.
                                    @endif
                                @else
                                    Your inspection for <strong
                                        style="color:#111827;">{{ $inspection->equipment->eqm_name }}</strong> has been
                                    successfully recorded. Thank you for conducting the inspection.
                                @endif
                            </p>

                            <!-- Details Card -->
                            <!-- DESKTOP (2-COLUMN) TABLE (> 600px) -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                class="detail-table desktop-table"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                <tr style="background-color:#f3f4f6;">
                                    <td colspan="2"
                                        style="padding:10px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        Inspection Details
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;width:35%;border-right:1px solid #e5e7eb;">
                                        Inspection #</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $inspection->ins_no }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;width:35%;border-right:1px solid #e5e7eb;">
                                        Equipment</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $inspection->equipment->eqm_name }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Department</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $inspection->department->dep_name }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Conducted By</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $inspection->conductedBy->user_fname }}
                                        {{ $inspection->conductedBy->user_lname }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Inspection Date</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ \Carbon\Carbon::parse($inspection->ins_dt)->format('M d, Y | h:i A') }}</td>
                                </tr>
                            </table>

                            <!-- MOBILE (1-COLUMN) TABLE (<= 600px) -->
                            <table width="100%" cellpadding="0" cellspacing="0" class="detail-table mobile-table"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                <tr style="background-color:#f3f4f6;">
                                    <td style="padding:10px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        Inspection Details
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Inspection #</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $inspection->ins_no }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Equipment</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $inspection->equipment->eqm_name }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Department</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $inspection->department->dep_name }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Conducted By</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $inspection->conductedBy->user_fname }}
                                        {{ $inspection->conductedBy->user_lname }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Inspection Date</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ \Carbon\Carbon::parse($inspection->ins_dt)->format('M d, Y | h:i A') }}</td>
                                </tr>
                            </table>

                            @if ($hasFailedItems)
                                <!-- Failed Items -->
                                <!-- DESKTOP (2-COLUMN) TABLE (> 600px) -->
                                <table width="100%" cellpadding="0" cellspacing="0" class="detail-table desktop-table"
                                    style="background-color:#fef2f2;border:1px solid #fecaca;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                    <tr style="background-color:#fee2e2;">
                                        <td colspan="2"
                                            style="padding:10px 16px;font-size:11px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:0.05em;">
                                            Failed Items ({{ $failedItems->count() }})
                                        </td>
                                    </tr>
                                    @foreach ($failedItems as $item)
                                        <tr
                                            style="border-top:1px solid #fecaca;{{ $loop->even ? 'background-color:#ffffff;' : '' }}">
                                            <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                                {{ $item->insi_cli_name_for_record }}</td>
                                            <td
                                                style="padding:12px 16px;font-size:13px;color:#6b7280;text-align:right;">
                                                {{ $item->insi_remarks ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                                <!-- MOBILE (1-COLUMN) TABLE (<= 600px) -->
                                <table width="100%" cellpadding="0" cellspacing="0" class="detail-table mobile-table"
                                    style="background-color:#fef2f2;border:1px solid #fecaca;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                    <tr style="background-color:#fee2e2;">
                                        <td style="padding:10px 16px;font-size:11px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:0.05em;">
                                            Failed Items ({{ $failedItems->count() }})
                                        </td>
                                    </tr>
                                    @foreach ($failedItems as $item)
                                        <tr style="border-top:1px solid #fecaca;{{ $loop->even ? 'background-color:#ffffff;' : '' }}">
                                        <td style="padding:12px 16px 2px 16px;font-size:13px;font-weight:600;color:#111827;">
                                            {{ $item->insi_cli_name_for_record }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 16px 12px 16px;font-size:12px;color:#6b7280;">
                                            {{ $item->insi_remarks ?: '—' }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            @endif

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0" class="cta-table">
                                <tr>
                                    <td align="center" style="border-radius:6px;background-color:{{ $recipientType === 'manager' && $hasFailedItems ? '#d97706' : '#16a34a' }};">
                                        <a href="{{ config('app.url') }}/inspections/{{ $inspection->ins_id }}" class="cta-button"
                                            style="display:inline-block;padding:12px 28px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                                            View Inspection →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer" style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 40px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                This is a system-generated email. Please do not reply to this message.
                            </p>
                            <p style="margin:6px 0 0;font-size:12px;color:#d1d5db;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
