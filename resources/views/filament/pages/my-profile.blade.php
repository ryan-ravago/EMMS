<x-filament-panels::page>
    {{ $this->profileInfolist($this->getInfolist('profileInfolist')) }}

    @if ($this->canReceiveMovementEmails())
        <x-filament::section icon="heroicon-o-envelope" icon-color="primary">
            <x-slot name="heading">Email notifications</x-slot>
            <x-slot name="description">
                Your role can be emailed about equipment and service vehicles.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>
    @endif
</x-filament-panels::page>
