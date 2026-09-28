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

                    <tr>
                        <td class="email-header" style="background-color:#10b981;padding:30px 40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">🚫 Cancellation Confirmed
                            </h1>
                            <p style="margin:6px 0 0;color:#e5e7eb;font-size:13px;">You have successfully cancelled this
                                work order.</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-body" style="padding:32px 40px;">
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                You have cancelled the following work order. Assigned technicians have been notified.
                            </p>

                            <!-- DESKTOP (2-COLUMN) TABLE (> 600px) -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                class="detail-table desktop-table"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                <tr style="background-color:#f3f4f6;">
                                    <td colspan="2"
                                        style="padding:10px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        Work Order Details
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;width:35%;border-right:1px solid #e5e7eb;">
                                        WO No.</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $workOrder->wo_no }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Equipment</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->equipment?->eqm_name ?? 'N/A' }}</td>
                                </tr>
                                {{-- <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Subject</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_title }}</td>
                                </tr> --}}
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Requestor Problem Description</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_req_desc ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Manager Problem Description</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_desc ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Priority</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->priority?->prio_name }}</td>
                                </tr>

                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Created By</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ trim(($workOrder->createdBy?->user_fname ?? '') . ' ' . ($workOrder->createdBy?->user_lname ?? '')) ?: 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Date & Time Created</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_created_at ?? $workOrder->created_at ? \Illuminate\Support\Carbon::parse($workOrder->wo_created_at ?? $workOrder->created_at)->format('M d, Y h:i A') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Technicians</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->workers?->map(fn($worker) => trim(($worker->user_fname ?? '') . ' ' . ($worker->user_lname ?? '')))->filter()->implode(', ') ?: 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Cancelled By</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $canceller->user_fname }} {{ $canceller->user_lname }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td colspan="2" style="padding:12px 16px;">
                                        <p
                                            style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                            Reason for Cancellation</p>
                                        <p
                                            style="margin:0;font-size:13px;color:#111827;background-color:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:10px 12px;line-height:1.6;">
                                            {{ $reason }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- MOBILE (1-COLUMN) TABLE (<= 600px) -->
                            <table width="100%" cellpadding="0" cellspacing="0" class="detail-table mobile-table"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                <tr style="background-color:#f3f4f6;">
                                    <td style="padding:10px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        Work Order Details
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        WO No.</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $workOrder->wo_no }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Equipment</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->equipment?->eqm_name ?? 'N/A' }}</td>
                                </tr>
                                {{-- <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Subject</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_title }}</td>
                                </tr> --}}
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Requestor Problem Description</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_req_desc ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Manager Problem Description</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_desc ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Priority</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->priority?->prio_name }}</td>
                                </tr>

                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Created By</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ trim(($workOrder->createdBy?->user_fname ?? '') . ' ' . ($workOrder->createdBy?->user_lname ?? '')) ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Date & Time Created</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->wo_created_at ?? $workOrder->created_at ? \Illuminate\Support\Carbon::parse($workOrder->wo_created_at ?? $workOrder->created_at)->format('M d, Y h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Technicians</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $workOrder->workers?->map(fn($worker) => trim(($worker->user_fname ?? '') . ' ' . ($worker->user_lname ?? '')))->filter()->implode(', ') ?: 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td style="padding:12px 16px 2px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">
                                        Cancelled By</td>
                                </tr>
                                <tr style="background-color:#ffffff;">
                                    <td style="padding:0 16px 12px 16px;font-size:13px;color:#111827;">
                                        {{ $canceller->user_fname }} {{ $canceller->user_lname }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:12px 16px;">
                                        <p
                                            style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                            Reason for Cancellation</p>
                                        <p
                                            style="margin:0;font-size:13px;color:#111827;background-color:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:10px 12px;line-height:1.6;">
                                            {{ $reason }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table cellpadding="0" cellspacing="0" class="cta-table">
                                <tr>
                                    <td align="center" style="border-radius:6px;background-color:#10b981;">
                                        <a href="{{ config('app.url') . '/work-orders/' . $workOrder->wo_id }}" class="cta-button"
                                            style="display:inline-block;padding:12px 28px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                                            View Work Order →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

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
