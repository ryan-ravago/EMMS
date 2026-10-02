<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\Inspections\InspectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInspection extends ViewRecord
{
    use HasRecordNavigation;

    protected function getRecordNavigationOrder(): array
    {
        return ['ins_dt', 'desc'];
    }

    protected static string $resource = InspectionResource::class;

    public function getTitle(): string
    {
        return "Inspection – {$this->record->equipment->eqm_name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
