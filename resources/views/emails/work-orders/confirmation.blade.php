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
                        <td style="background-color:#16a34a;padding:30px 40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">✅ Work Order Confirmed
                            </h1>
                            <p style="margin:6px 0 0;color:#bbf7d0;font-size:13px;">A new work order has been created
                                and is pending your acknowledgment.</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 40px;">
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                Please review the details below and acknowledge the work order at your earliest
                                convenience.
                            </p>

                            <!-- Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0"
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
                            </table>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:6px;background-color:#16a34a;">
                                        <a href="{{ config('app.url') }}/{{ $workOrder->createdBy?->hasRole('requestor') ? 'requestor-work-orders' : 'work-orders' }}/{{ $workOrder->wo_id }}"
                                            style="display:inline-block;padding:12px 28px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                                            View Work Order →
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
