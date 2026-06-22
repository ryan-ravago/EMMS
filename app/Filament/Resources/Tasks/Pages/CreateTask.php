<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (in_array(auth()->user()?->department?->dep_code, ['MECH', 'ELEC'])) {
            $data['task_tut_id'] = 1;
        }

        return $data;
    }
}
