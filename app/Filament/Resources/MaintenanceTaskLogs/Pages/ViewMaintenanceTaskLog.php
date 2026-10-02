<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceTaskLog extends ViewRecord
{
    use HasRecordNavigation;

    protected function getRecordNavigationOrder(): array
    {
        return ['mtl_dt', 'desc'];
    }

    protected static string $resource = MaintenanceTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
