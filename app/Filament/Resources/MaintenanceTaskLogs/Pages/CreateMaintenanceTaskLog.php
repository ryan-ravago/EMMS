<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Pages;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceTaskLog extends CreateRecord
{
    protected static string $resource = MaintenanceTaskLogResource::class;
}
