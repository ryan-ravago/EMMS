<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family: sans-serif; font-size: 16px; color: #333; padding: 32px;">

    <h2 style="margin: 0 0 16px;">Maintenance Tasks Due</h2>

    <p style="margin: 0 0 16px;">
        You have {{ count($tasks) }} maintenance {{ count($tasks) === 1 ? 'task' : 'tasks' }} due today.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <thead>
            <tr>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Equipment</th>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Department</th>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Checklist</th>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Item</th>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Due Date</th>
                <th
                    style="text-align:left; padding: 10px 12px; border-bottom: 2px solid #e1e1e1; background: #f7f7f7; font-weight: 600;">
                    Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">{{ $task->eqm_name }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">{{ $task->dep_name }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">{{ $task->clt_name }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">{{ $task->mt_cli_log }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">
                        {{ \Carbon\Carbon::parse($task->mt_due_dt)->format('M d, Y | h:i A') }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #ebebeb;">{{ $task->status_title }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin: 0; font-size: 14px;">
        Don't reply because system generated<br>
        {{ config('app.name') }}
    </p>

</body>

</html>
