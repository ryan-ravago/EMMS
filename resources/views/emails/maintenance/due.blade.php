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
                        <td style="background-color:#dc2626;padding:30px 40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">⚙️ Preventive Maintenance
                                Due</h1>
                            <p style="margin:6px 0 0;color:#fecaca;font-size:13px;">Action Required</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 40px;">
                            <p style="margin:0 0 20px;font-size:15px;color:#374151;">
                                Hello <strong>Manager</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                Equipment maintenance is now due. Please schedule and complete the preventive
                                maintenance as soon as possible to maintain equipment reliability.
                            </p>

                            <!-- Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;">
                                <tr style="background-color:#f3f4f6;">
                                    <td colspan="2"
                                        style="padding:10px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        Equipment Details
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;width:35%;border-right:1px solid #e5e7eb;">
                                        Equipment Name</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;font-weight:600;">
                                        {{ $equipment->eqm_name }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Equipment Type</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $equipment->type?->eqmt_name ?? 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Model</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $equipment->equipmentModel?->eqmm_name ?? 'N/A' }}</td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Maintenance Interval</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        @php
                                            $intervals = [];
                                            if ($equipment->eqm_pm_itrv_years) {
                                                $intervals[] = "{$equipment->eqm_pm_itrv_years} year(s)";
                                            }
                                            if ($equipment->eqm_pm_itrv_months) {
                                                $intervals[] = "{$equipment->eqm_pm_itrv_months} month(s)";
                                            }
                                            if ($equipment->eqm_pm_itrv_weeks) {
                                                $intervals[] = "{$equipment->eqm_pm_itrv_weeks} week(s)";
                                            }
                                            if ($equipment->eqm_pm_itrv_days) {
                                                $intervals[] = "{$equipment->eqm_pm_itrv_days} day(s)";
                                            }
                                        @endphp
                                        {{ count($intervals) ? implode(', ', $intervals) : 'Not Set' }}
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Last PM Start Date</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#111827;">
                                        {{ $equipment->eqm_pm_itrv_start_date?->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;background-color:#ffffff;">
                                    <td
                                        style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">
                                        Next Due Date</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#dc2626;font-weight:600;">
                                        {{ $equipment->eqm_next_pm_due_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:6px;background-color:#dc2626;">
                                        <a href="{{ route('filament.admin.resources.equipment.view', ['record' => $equipment->eqm_id]) }}"
                                            style="display:inline-block;padding:12px 28px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                                            Schedule Maintenance →
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
                                This is an automated notification from EMMS
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
