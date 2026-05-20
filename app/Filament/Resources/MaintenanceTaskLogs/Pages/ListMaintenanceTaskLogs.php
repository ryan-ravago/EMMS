<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Pages;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceTaskLogs extends ListRecords
{
    protected static string $resource = MaintenanceTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
