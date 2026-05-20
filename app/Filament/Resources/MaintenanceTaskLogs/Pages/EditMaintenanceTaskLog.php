<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Pages;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceTaskLog extends EditRecord
{
    protected static string $resource = MaintenanceTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
