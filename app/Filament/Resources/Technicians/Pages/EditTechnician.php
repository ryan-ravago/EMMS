<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Filament\Support\UserDeleteActions;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTechnician extends EditRecord
{
    protected static string $resource = TechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            UserDeleteActions::delete(),
        ];
    }
}
