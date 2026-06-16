<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Site Name & Logo --}}
        <x-filament::section>
            <x-slot name="heading">Brand Settings</x-slot>
            <x-slot name="description">Customize the site name, logo, and primary color theme.</x-slot>

            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-o-check">
                        Save Settings
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    </div>
</x-filament-panels::page>
