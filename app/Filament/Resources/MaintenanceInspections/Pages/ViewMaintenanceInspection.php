<?php

namespace App\Filament\Resources\MaintenanceInspections\Pages;

use App\Filament\Concerns\HasDetailsTab;
use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\MaintenanceInspections\MaintenanceInspectionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceInspection extends ViewRecord
{
    use HasDetailsTab;
    use HasRecordNavigation;

    protected static string $resource = MaintenanceInspectionResource::class;

    /**
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    protected function getRecordNavigationOrder(): array
    {
        return ['closed_at', 'desc'];
    }
}
