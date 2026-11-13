<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Equipment Trip #{{ $trip->id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12pt;
            padding-bottom: 6pt;
            border-bottom: 2pt solid #1e40af;
        }

        .header .site {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1pt;
        }

        .header h1 {
            font-size: 18pt;
            color: #1e40af;
            margin: 2pt 0 0;
        }

        .section {
            margin-bottom: 10pt;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1.5pt solid #dbeafe;
            padding-bottom: 3pt;
            margin-bottom: 6pt;
            text-transform: uppercase;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
        }

        table.details td {
            padding: 2pt 0;
            vertical-align: top;
            border-bottom: 0.5pt solid #eee;
        }

        table.details td.label {
            width: 32%;
            font-weight: bold;
            color: #555;
            padding-right: 8pt;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid th {
            background: #1e40af;
            color: #fff;
            text-align: left;
            padding: 4pt 6pt;
            font-size: 9pt;
        }

        table.grid td {
            padding: 4pt 6pt;
            border-bottom: 0.5pt solid #ddd;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 1pt 6pt;
            border: 1pt solid #15803d;
            color: #15803d;
            font-weight: bold;
            font-size: 9pt;
        }

        .signatures {
            width: 100%;
            margin-top: 28pt;
            border-collapse: collapse;
        }

        .signatures td {
            width: 33%;
            padding: 0 8pt;
            text-align: center;
            font-size: 9pt;
            color: #555;
        }

        .signatures .line {
            border-top: 1pt solid #333;
            padding-top: 3pt;
        }

        .footer {
            margin-top: 22pt;
            padding-top: 6pt;
            border-top: 0.5pt solid #ccc;
            font-size: 8pt;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="site">{{ $siteName }}</div>
        <h1>Equipment Trip Record</h1>
    </div>

    <div class="section">
        <div class="section-title">Trip Summary</div>
        <table class="details">
            <tr><td class="label">Trip No.</td><td>{{ $trip->id }}</td></tr>
            <tr><td class="label">Status</td><td><span class="badge">{{ $trip->status_label }}</span></td></tr>
            <tr><td class="label">Away For</td><td>{{ $trip->duration ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Equipment Details</div>
        <table class="details">
            <tr><td class="label">Equipment Code</td><td>{{ $trip->asset?->eqm_prc_code ?? '—' }}</td></tr>
            <tr><td class="label">Name</td><td>{{ $trip->asset?->eqm_name ?? '—' }}</td></tr>
            <tr><td class="label">Asset Type</td><td>{{ $trip->asset?->assetType?->name ?? '—' }}</td></tr>
            <tr><td class="label">Equipment Type</td><td>{{ $trip->asset?->type?->eqmt_name ?? '—' }}</td></tr>
            <tr><td class="label">Brand</td><td>{{ $trip->asset?->brand?->eqmb_name ?? '—' }}</td></tr>
            <tr><td class="label">Model</td><td>{{ $trip->asset?->equipmentModel?->eqmm_name ?? '—' }}</td></tr>
            <tr><td class="label">Serial No.</td><td>{{ $trip->asset?->eqm_serial_num ?: '—' }}</td></tr>
            <tr><td class="label">Plate No.</td><td>{{ $trip->asset?->eqm_plate_num ?: '—' }}</td></tr>
            <tr><td class="label">Current Location</td><td>{{ $trip->asset?->location?->full_path ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Movement Details</div>
        <table class="grid">
            <thead>
                <tr><th style="width:14%">Movement</th><th style="width:26%">Date and Time</th><th style="width:30%">Yard</th><th style="width:30%">Recorded As</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Time Out</td>
                    <td>{{ $trip->time_out?->format('M d, Y h:i A') ?? 'Missing' }}</td>
                    <td>{{ $trip->yardOut?->name ?? '—' }}</td>
                    <td>@if ($trip->time_out === null) — @elseif ($trip->isManualOut()) Entered manually @else Scanned (tag {{ $trip->outLog?->tag_id ?? $trip->tag_id ?? '—' }}) @endif</td>
                </tr>
                <tr>
                    <td>Time In</td>
                    <td>{{ $trip->time_in?->format('M d, Y h:i A') ?? 'Missing' }}</td>
                    <td>{{ $trip->yardIn?->name ?? '—' }}</td>
                    <td>@if ($trip->time_in === null) — @elseif ($trip->isManualIn()) Entered manually @else Scanned (tag {{ $trip->inLog?->tag_id ?? $trip->tag_id ?? '—' }}) @endif</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($trip->isManualOut() || $trip->isManualIn())
        <div class="section">
            <div class="section-title">Entered Manually</div>
            <table class="details">
                @if ($trip->isManualOut())
                    <tr><td class="label">Time Out Entered By</td><td>{{ $trip->manualOutBy?->full_name ?? '—' }}@if ($trip->manual_out_at), {{ $trip->manual_out_at->format('M d, Y h:i A') }}@endif</td></tr>
                    <tr><td class="label">Time Out Remarks</td><td>{{ $trip->manual_out_remarks ?? '—' }}</td></tr>
                @endif
                @if ($trip->isManualIn())
                    <tr><td class="label">Time In Entered By</td><td>{{ $trip->manualInBy?->full_name ?? '—' }}@if ($trip->manual_in_at), {{ $trip->manual_in_at->format('M d, Y h:i A') }}@endif</td></tr>
                    <tr><td class="label">Time In Remarks</td><td>{{ $trip->manual_in_remarks ?? '—' }}</td></tr>
                @endif
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Acknowledgement</div>
        <table class="details">
            <tr><td class="label">Acknowledged By</td><td>{{ $trip->acknowledgedBy?->full_name ?? '—' }}</td></tr>
            <tr><td class="label">Acknowledged At</td><td>{{ $trip->acknowledged_at?->format('M d, Y h:i A') ?? '—' }}</td></tr>
            <tr><td class="label">Remarks</td><td>{{ $trip->acknowledgement_remarks ?? '—' }}</td></tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td><div class="line">Prepared by</div></td>
            <td><div class="line">Checked by</div></td>
            <td><div class="line">Received by</div></td>
        </tr>
    </table>

    <div class="footer">
        Printed by {{ $printedBy }} on {{ $printedAt->format('M d, Y h:i A') }}. This copy reflects the trip as it was acknowledged.
    </div>
</body>

</html>
