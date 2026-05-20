<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Pages;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceTaskLog extends ViewRecord
{
    protected static string $resource = MaintenanceTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
