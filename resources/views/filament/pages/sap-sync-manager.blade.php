<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Schedule Info --}}
        <x-filament::section>
            <x-slot name="heading">Schedule Info</x-slot>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <p>⏰ <strong>Automatic sync</strong> runs daily at the time set below.</p>
                <p>🔁 Manual sync can be triggered anytime using the <strong>Sync Now</strong> button above.</p>
            </div>
        </x-filament::section>

        {{-- Schedule Time Form --}}
        <x-filament::section>
            <x-slot name="heading">Daily Sync Schedule</x-slot>

            <form wire:submit="saveSchedule">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-o-check">
                        Save Schedule
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Recent runs --}}
        <x-filament::section>
            <x-slot name="heading">Recent Syncs</x-slot>

            @php
                $runs = $this->getRecentSyncs();
                $cell = 'padding:.4rem .75rem .4rem 0;vertical-align:top;border-bottom:1px solid rgba(128,128,128,.25);';
            @endphp

            @if ($runs->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No sync runs recorded yet.</p>
            @else
                <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                    <thead>
                        <tr style="text-align:left;opacity:.65;">
                            <th style="{{ $cell }}">When</th>
                            <th style="{{ $cell }}">By</th>
                            <th style="{{ $cell }}">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($runs as $run)
                            <tr @if ($run->getExtraProperty('status') === 'failed') style="color:#dc2626;" @endif>
                                <td style="{{ $cell }}white-space:nowrap;">{{ $run->created_at->format('M d, Y h:i A') }}</td>
                                <td style="{{ $cell }}">{{ $run->causer ? trim($run->causer->user_fname.' '.$run->causer->user_lname) : 'Schedule' }}</td>
                                <td style="{{ $cell }}word-break:break-word;">{{ $run->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-sm text-gray-600 dark:text-gray-400" style="margin-top:.5rem;">Full history is in the Activity Log.</p>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
