<?php

namespace App\Filament\Resources\AdminManagers\Pages;

use App\Filament\Resources\AdminManagers\AdminManagerResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminManager extends CreateRecord
{
    protected static string $resource = AdminManagerResource::class;

    protected function afterCreate(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin_manager', 'guard_name' => 'web'],
            ['display_name' => 'Admin Manager'],
        );

        $this->record->assignRole($role);
    }
}
