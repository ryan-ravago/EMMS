<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- Side by side on wide screens; stacks when the screen is too narrow (e.g. mobile) --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr)); gap: 1.5rem; align-items: start;">

        {{-- Schedule Info --}}
        <x-filament::section>
            <x-slot name="heading">Schedule Info</x-slot>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <p>⏰ <strong>Automatic sync</strong> runs daily at the time set in <strong>Daily Sync Schedule</strong>.</p>
                <p>🔁 Manual sync can be triggered anytime using the <strong>Sync Now</strong> button above.</p>
            </div>
        </x-filament::section>

        {{-- Schedule Time Form --}}
        <x-filament::section>
            <x-slot name="heading">Daily Sync Schedule</x-slot>

            <form wire:submit="saveSchedule">
                {{ $this->form }}

                <div style="margin-top: 1rem;">
                    <x-filament::button type="submit" icon="heroicon-o-check">
                        Save Schedule
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        </div>

        {{-- Recent syncs: always its own full-width row, below the two cards --}}
        <div style="width: 100%; min-width: 0;">
            {{ $this->table }}
        </div>

    </div>
</x-filament-panels::page>
