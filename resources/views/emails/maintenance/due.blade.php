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

                    <tr>
                        <td
                            style="background-color: {{ $type === 'overdue' ? '#b91c1c' : '#1d4ed8' }}; padding:30px 40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">
                                {{ $type === 'overdue' ? '⚠️ Overdue Maintenance Notice' : '📅 Tasks Due Today' }}
                            </h1>
                            <p style="margin:6px 0 0;color:#bfdbfe;font-size:13px;">
                                {{ $type === 'overdue' ? 'Action required: Some tasks require immediate attention.' : 'Daily digest for your assigned department.' }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 40px;">
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                @if ($type === 'overdue')
                                    You have <strong style="color:#111827;">{{ $tasks->count() }}</strong> overdue
                                    maintenance {{ $tasks->count() === 1 ? 'task' : 'tasks' }} that still need
                                    attention. Please review the schedule logs below.
                                @else
                                    You have <strong style="color:#111827;">{{ $tasks->count() }}</strong> maintenance
                                    {{ $tasks->count() === 1 ? 'task' : 'tasks' }} due today.
                                @endif
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:28px;border-collapse: collapse;">
                                <thead>
                                    <tr style="background-color:#f3f4f6;">
                                        <th
                                            style="padding:10px 12px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;text-align:left;border-bottom:1px solid #e5e7eb;">
                                            Equipment</th>
                                        <th
                                            style="padding:10px 12px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;text-align:left;border-bottom:1px solid #e5e7eb;">
                                            Task</th>
                                        <th
                                            style="padding:10px 12px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;text-align:left;border-bottom:1px solid #e5e7eb;">
                                            Due Date</th>
                                        <th
                                            style="padding:10px 12px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;text-align:left;border-bottom:1px solid #e5e7eb;">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tasks as $task)
                                        <tr
                                            style="border-bottom:1px solid #e5e7eb; {{ $type === 'overdue' ? 'background-color: #fff5f5;' : 'background-color: #ffffff;' }}">
                                            <td
                                                style="padding:12px;font-size:13px;color:#111827;border-bottom:1px solid #e5e7eb;">
                                                <div style="font-weight:600;color:#111827;">{{ $task->eqm_name }}</div>
                                                <div style="font-size:11px;color:#6b7280;">{{ $task->dep_name }}</div>
                                            </td>
                                            <td
                                                style="padding:12px;font-size:13px;color:#374151;border-bottom:1px solid #e5e7eb;">
                                                {{ $task->task_name }}
                                            </td>
                                            <td
                                                style="padding:12px;font-size:13px;border-bottom:1px solid #e5e7eb; {{ $type === 'overdue' ? 'color: #b91c1c; font-weight: 600;' : 'color: #374151;' }}">
                                                {{ \Carbon\Carbon::parse($task->mt_due_dt)->format('M d, Y') }}
                                                <div style="font-size:11px;color:#6b7280;font-weight:normal;">
                                                    {{ \Carbon\Carbon::parse($task->mt_due_dt)->format('h:i A') }}
                                                </div>
                                            </td>
                                            <td
                                                style="padding:12px;font-size:13px;color:#374151;border-bottom:1px solid #e5e7eb;">
                                                <span
                                                    style="font-size:12px;padding:2px 6px;border-radius:4px;{{ $type === 'overdue' ? 'background-color:#fee2e2;color:#991b1b;' : 'background-color:#f3f4f6;color:#1f2937;' }}">
                                                    {{ $task->status_title }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="border-radius:6px;background-color: {{ $type === 'overdue' ? '#b91c1c' : '#1d4ed8' }};">
                                        <a href="{{ config('app.url') . '/maintenance-tasks' }}"
                                            style="display:inline-block;padding:12px 28px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                                            Open Maintenance Panel →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 40px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                This is an automated system notification. Please do not reply directly to this mailbox.
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
