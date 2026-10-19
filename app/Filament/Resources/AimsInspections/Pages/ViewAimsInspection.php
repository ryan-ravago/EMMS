<?php

namespace App\Filament\Resources\AimsInspections\Pages;

use App\Filament\Concerns\HasDetailsTab;
use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\AimsInspections\AimsInspectionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAimsInspection extends ViewRecord
{
    use HasDetailsTab;
    use HasRecordNavigation;

    protected static string $resource = AimsInspectionResource::class;

    /**
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    protected function getRecordNavigationOrder(): array
    {
        return ['closed_at', 'desc'];
    }
}
