<x-filament-panels::page>
    @php($summary = $this->getSummary())

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <x-filament::section>
            <x-slot name="heading">Total</x-slot>

            <div class="text-3xl font-semibold text-gray-950 dark:text-white">
                {{ number_format($summary['total']) }}
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">matching the current filters</p>
        </x-filament::section>

        @foreach ($summary['groups'] as $title => $counts)
            <x-filament::section>
                <x-slot name="heading">{{ $title }}</x-slot>

                <dl style="display: grid; gap: 0.375rem;">
                    @foreach ($counts as $label => $count)
                        <div style="display: flex; justify-content: space-between; gap: 1rem;">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($count) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-filament::section>
        @endforeach
    </div>

    {{ $this->table }}
</x-filament-panels::page>
