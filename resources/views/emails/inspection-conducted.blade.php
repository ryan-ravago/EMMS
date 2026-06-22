<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background-color:{{ $recipientType === 'manager' && $hasFailedItems ? '#d97706' : '#16a34a' }};padding:30px 40px;">
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
                        <td style="padding:32px 40px;">
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
                            <table width="100%" cellpadding="0" cellspacing="0"
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

                            @if ($hasFailedItems)
                                <!-- Failed Items -->
                                <table width="100%" cellpadding="0" cellspacing="0"
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
                            @endif

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="border-radius:6px;background-color:{{ $recipientType === 'manager' && $hasFailedItems ? '#d97706' : '#16a34a' }};">
                                        <a href="{{ config('app.url') }}/inspections/{{ $inspection->ins_id }}"
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
                        <td
                            style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 40px;text-align:center;">
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
