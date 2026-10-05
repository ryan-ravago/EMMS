@php
    $muted = 'opacity:.65;';
    $cell = 'padding:.4rem .75rem .4rem 0;vertical-align:top;border-bottom:1px solid rgba(128,128,128,.25);';
@endphp

<div style="display:grid;gap:1.25rem;font-size:.875rem;">
    <table style="width:100%;border-collapse:collapse;">
        <tbody>
            <tr><td style="{{ $cell }}{{ $muted }}width:9rem;">When</td><td style="{{ $cell }}">{{ $when }}</td></tr>
            <tr><td style="{{ $cell }}{{ $muted }}">User</td><td style="{{ $cell }}">{{ $user }}</td></tr>
            <tr><td style="{{ $cell }}{{ $muted }}">Action</td><td style="{{ $cell }}">{{ $actionLabel }}</td></tr>
            @if ($recordName)
                <tr><td style="{{ $cell }}{{ $muted }}">Record</td><td style="{{ $cell }}">{{ $recordType }}: {{ $recordName }}</td></tr>
            @endif
            @if ($via)
                <tr><td style="{{ $cell }}{{ $muted }}">Triggered by</td><td style="{{ $cell }}">{{ $via }}</td></tr>
            @endif
            <tr><td style="{{ $cell }}{{ $muted }}">Summary</td><td style="{{ $cell }}">{{ $description }}</td></tr>
        </tbody>
    </table>

    @if (count($changes))
        <div>
            <div style="font-weight:600;margin-bottom:.4rem;">Changes</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;{{ $muted }}">
                        <th style="{{ $cell }}">Field</th>
                        <th style="{{ $cell }}">Before</th>
                        <th style="{{ $cell }}">After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($changes as $change)
                        <tr>
                            <td style="{{ $cell }}font-weight:500;">{{ $change['field'] }}</td>
                            <td style="{{ $cell }}word-break:break-word;">{{ $change['old'] }}</td>
                            <td style="{{ $cell }}word-break:break-word;">{{ $change['new'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($extra))
        <div>
            <div style="font-weight:600;margin-bottom:.4rem;">Details</div>
            <table style="width:100%;border-collapse:collapse;">
                <tbody>
                    @foreach ($extra as $label => $value)
                        <tr>
                            <td style="{{ $cell }}{{ $muted }}width:9rem;">{{ $label }}</td>
                            <td style="{{ $cell }}word-break:break-word;">{{ $value }}</td>
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
                <div style="{{ $muted }}margin-top:.4rem;">+ {{ $batchTotal - count($batchItems) }} more</div>
            @endif
        </div>
    @endif
</div>
