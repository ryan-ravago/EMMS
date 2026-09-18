<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;

class CreateTechnician extends CreateRecord
{
    protected static string $resource = TechnicianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Technicians inherit their department from the manager who creates them.
        $data['user_dep_id'] = auth()->user()->user_dep_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'technician', 'guard_name' => 'web'],
            ['display_name' => 'Technician'],
        );

        $this->record->assignRole($role);
    }
}
