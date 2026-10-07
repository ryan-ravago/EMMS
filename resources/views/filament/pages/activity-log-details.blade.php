<style>
    .emms-details table { width: 100%; border-collapse: collapse; }
    .emms-details th,
    .emms-details td { padding: .4rem .75rem .4rem 0; vertical-align: top; border-bottom: 1px solid rgba(128, 128, 128, .25); }
    .emms-details thead tr { text-align: left; opacity: .65; }
    .emms-details .emms-muted { opacity: .65; }
    .emms-details .emms-wrap { word-break: break-word; }
    .emms-details .emms-kv td:first-child { width: 9rem; }
    .emms-details .emms-field { font-weight: 500; }

    /* Phones: no side-by-side columns. Each row becomes a stacked block. */
    @media (width < 40rem) {
        .emms-details table,
        .emms-details tbody,
        .emms-details tr,
        .emms-details td { display: block; width: 100%; }

        .emms-details tr { padding-block: .5rem; border-bottom: 1px solid rgba(128, 128, 128, .25); }
        .emms-details td { padding: 0; border: 0; }

        /* Label above value. */
        .emms-details .emms-kv td:first-child { width: 100%; font-size: .75rem; }
        .emms-details .emms-kv td:last-child { margin-top: .125rem; }

        /* Field / Before / After: the header row is replaced by a small label on each value. */
        .emms-details .emms-changes thead { display: none; }
        .emms-details .emms-changes td + td { margin-top: .25rem; }
        .emms-details .emms-changes td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: .75rem;
            opacity: .65;
        }
    }
</style>

<div class="emms-details" style="display:grid;gap:1.25rem;font-size:.875rem;">
    <table class="emms-kv">
        <tbody>
            <tr><td class="emms-muted">When</td><td>{{ $when }}</td></tr>
            <tr><td class="emms-muted">User</td><td>{{ $user }}</td></tr>
            <tr><td class="emms-muted">Action</td><td>{{ $actionLabel }}</td></tr>
            @if ($recordName)
                <tr><td class="emms-muted">Record</td><td>{{ $recordType }}: {{ $recordName }}</td></tr>
            @endif
            @if ($via)
                <tr><td class="emms-muted">Triggered by</td><td>{{ $via }}</td></tr>
            @endif
            <tr><td class="emms-muted">Summary</td><td>{{ $description }}</td></tr>
        </tbody>
    </table>

    @if (count($changes))
        <div>
            <div style="font-weight:600;margin-bottom:.4rem;">Changes</div>
            <table class="emms-changes">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Before</th>
                        <th>After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($changes as $change)
                        <tr>
                            <td class="emms-field">{{ $change['field'] }}</td>
                            <td class="emms-wrap" data-label="Before">{{ $change['old'] }}</td>
                            <td class="emms-wrap" data-label="After">{{ $change['new'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($extra))
        <div>
            <div style="font-weight:600;margin-bottom:.4rem;">Details</div>
            <table class="emms-kv">
                <tbody>
                    @foreach ($extra as $label => $value)
                        <tr>
                            <td class="emms-muted">{{ $label }}</td>
                            <td class="emms-wrap">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($batchTotal > 1)
        <div>
            <div style="font-weight:600;margin-bottom:.4rem;">Part of a bulk action ({{ $batchTotal }} records)</div>
            <ul style="margin:0;padding-left:1.1rem;">
                @foreach ($batchItems as $item)
                    <li style="margin-bottom:.2rem;">{{ $item }}</li>
                @endforeach
            </ul>
            @if ($batchTotal > count($batchItems))
                <div class="emms-muted" style="margin-top:.4rem;">+ {{ $batchTotal - count($batchItems) }} more</div>
            @endif
        </div>
    @endif
</div>
