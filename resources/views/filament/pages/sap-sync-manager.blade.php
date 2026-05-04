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

    </div>
</x-filament-panels::page>
