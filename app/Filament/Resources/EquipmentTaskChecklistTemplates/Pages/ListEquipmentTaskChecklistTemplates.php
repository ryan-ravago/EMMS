<?php

namespace App\Filament\Resources\EquipmentTaskChecklistTemplates\Pages;

use App\Filament\Resources\EquipmentTaskChecklistTemplates\EquipmentTaskChecklistTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentTaskChecklistTemplates extends ListRecords
{
    protected static string $resource = EquipmentTaskChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
