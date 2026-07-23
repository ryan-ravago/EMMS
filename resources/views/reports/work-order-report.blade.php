<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order Report - {{ $workOrder->wo_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12.7mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 0;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 20pt;
            padding-bottom: 10pt;
            border-bottom: 2pt solid #1e40af;
        }

        .header h1 {
            font-size: 18pt;
            color: #1e40af;
            margin-bottom: 4pt;
        }

        .header p {
            font-size: 9pt;
            color: #666;
        }

        .section {
            margin-bottom: 16pt;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1.5pt solid #dbeafe;
            padding-bottom: 3pt;
            margin-bottom: 8pt;
            text-transform: uppercase;
        }

        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 8pt;
        }

        .details-row {
            display: table-row;
        }

        .details-label {
            display: table-cell;
            font-weight: bold;
            padding: 3pt 8pt 3pt 0;
            width: 140pt;
            vertical-align: top;
            color: #555;
        }

        .details-value {
            display: table-cell;
            padding: 3pt 0;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 2pt 8pt;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-pending {
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #f59e0b;
        }

        .badge-inprog {
            color: #1e40af;
            background: #dbeafe;
            border: 1px solid #3b82f6;
        }

        .badge-completed,
        .badge-cmp {
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #10b981;
        }

        .badge-cancelled,
        .badge-cnc {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #ef4444;
        }

        .badge-rejected,
        .badge-rej,
        .badge-rca {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #dc2626;
        }

        .badge-pca {
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #f59e0b;
        }

        .badge-pnd {
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #f59e0b;
        }

        .badge-default {
            color: #374151;
            background: #f3f4f6;
            border: 1px solid #6b7280;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8pt;
        }

        .table th {
            background-color: #f3f4f6;
            border: 0.5pt solid #d1d5db;
            padding: 5pt 6pt;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
        }

        .table td {
            border: 0.5pt solid #d1d5db;
            padding: 5pt 6pt;
            font-size: 9pt;
            vertical-align: top;
        }

        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .technician-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .technician-list li {
            padding: 2pt 0;
            border-bottom: 0.5pt dotted #e5e7eb;
        }

        .technician-list li:last-child {
            border-bottom: none;
        }

        .footer {
            margin-top: 20pt;
            padding-top: 8pt;
            border-top: 0.5pt solid #d1d5db;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }

        .no-data {
            color: #999;
            font-style: italic;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>Work Order Report</h1>
            <p>WO #{{ $workOrder->wo_no }} &mdash; Generated on {{ now()->format('M d, Y \a\t h:i A') }}</p>
        </div>

        {{-- Work Order Details --}}
        <div class="section">
            <div class="section-title">Work Order Details</div>
            <div class="details-grid">
                <div class="details-row">
                    <div class="details-label">WO Number:</div>
                    <div class="details-value">{{ $workOrder->wo_no }}</div>
                </div>
                <div class="details-row">
                    <div class="details-label">Equipment:</div>
                    <div class="details-value">{{ $workOrder->equipment?->eqm_name ?? 'N/A' }}</div>
                </div>
                @if (filled($workOrder->wo_req_desc))
                    <div class="details-row">
                        <div class="details-label">Requestor Description:</div>
                        <div class="details-value">{{ $workOrder->wo_req_desc }}</div>
                    </div>
                @endif
                <div class="details-row">
                    <div class="details-label">
                        {{ filled($workOrder->wo_req_desc) ? 'Manager Description:' : 'Description:' }}</div>
                    <div class="details-value">{{ $workOrder->wo_desc ?? '-' }}</div>
                </div>
                <div class="details-row">
                    <div class="details-label">Status:</div>
                    <div class="details-value">
                        <span class="badge badge-{{ $workOrder->status?->status_id ?? 'default' }}">
                            {{ $workOrder->status?->status_title ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <div class="details-row">
                    <div class="details-label">Priority:</div>
                    <div class="details-value">{{ $workOrder->priority?->prio_name ?? 'N/A' }}</div>
                </div>

                <div class="details-row">
                    <div class="details-label">Department:</div>
                    <div class="details-value">{{ $workOrder->department?->dep_name ?? 'N/A' }}</div>
                </div>
                <div class="details-row">
                    <div class="details-label">Submitted By:</div>
                    <div class="details-value">{{ $workOrder->createdBy?->full_name ?? 'N/A' }}</div>
                </div>
                <div class="details-row">
                    <div class="details-label">Date Submitted:</div>
                    <div class="details-value">
                        {{ $workOrder->wo_created_dt ? \Carbon\Carbon::parse($workOrder->wo_created_dt)->format('M d, Y | h:i A') : 'N/A' }}
                    </div>
                </div>
                <div class="details-row">
                    <div class="details-label">Date Closed:</div>
                    <div class="details-value">
                        {{ $workOrder->wo_closed_dt ? \Carbon\Carbon::parse($workOrder->wo_closed_dt)->format('M d, Y | h:i A') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned Technicians --}}
        <div class="section">
            <div class="section-title">Assigned Technicians</div>
            @if ($workOrder->workers->count())
                <ul class="technician-list">
                    @foreach ($workOrder->workers as $worker)
                        <li>{{ $worker->user_fname }} {{ $worker->user_lname }}</li>
                    @endforeach
                </ul>
            @else
                <p class="no-data">No technicians assigned.</p>
            @endif
        </div>

        {{-- Updates --}}
        <div class="section">
            <div class="section-title">Updates</div>
            @if ($workOrder->logUpdates->count())
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Update Note</th>
                            <th style="width: 25%;">By</th>
                            <th style="width: 25%;">Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workOrder->logUpdates->sortByDesc('wolu_dt') as $update)
                            <tr>
                                <td>{{ $update->wolu_update_note }}</td>
                                <td>{{ $update->by?->user_fname }} {{ $update->by?->user_lname }}</td>
                                <td>{{ $update->wolu_dt ? \Carbon\Carbon::parse($update->wolu_dt)->format('M d, Y | h:i A') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data">No updates recorded.</p>
            @endif
        </div>

        {{-- History Logs --}}
        <div class="section">
            <div class="section-title">History Logs</div>
            @if ($workOrder->logs->count())
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Action</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 30%;">Note</th>
                            <th style="width: 15%;">By</th>
                            <th style="width: 20%;">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workOrder->logs as $log)
                            <tr>
                                <td>{{ $log->wol_a_log }}</td>
                                <td>{{ $log->wol_status_log }}</td>
                                <td>{{ $log->wol_note ?? '-' }}</td>
                                <td>{{ $log->by?->user_fname }} {{ $log->by?->user_lname }}</td>
                                <td>{{ $log->wol_dt ? \Carbon\Carbon::parse($log->wol_dt)->format('M d, Y | h:i A') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data">No history logs recorded.</p>
            @endif
        </div>

        {{-- Reports --}}
        {{-- <div class="section">
            <div class="section-title">Reports (Report Submissions)</div>
            @if ($workOrder->reportSubmissions->count())
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Work Date</th>
                            <th style="width: 25%;">Submitted By</th>
                            <th style="width: 30%;">Workers</th>
                            <th style="width: 25%;">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workOrder->reportSubmissions->sortByDesc('rs_submitted_dt') as $report)
                            <tr>
                                <td>{{ $report->rs_work_date ? \Carbon\Carbon::parse($report->rs_work_date)->format('M d, Y') : '-' }}
                                </td>
                                <td>{{ $report->submittedBy?->user_fname }} {{ $report->submittedBy?->user_lname }}
                                </td>
                                <td>
                                    @if ($report->workers->count())
                                        {{ $report->workers->map(fn($w) => $w->user_fname . ' ' . $w->user_lname)->implode(', ') }}
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                <td>{{ $report->rs_submitted_dt ? \Carbon\Carbon::parse($report->rs_submitted_dt)->format('M d, Y | h:i A') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data">No reports submitted.</p>
            @endif
        </div> --}}

        {{-- Footer --}}
        <div class="footer">
            <p>This report was generated by EMMS &mdash; Equipment Maintenance Management System</p>
        </div>
    </div>
</body>

</html>
