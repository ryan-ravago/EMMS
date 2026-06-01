<?php

namespace App\Filament\Resources\EquipmentTaskChecklistTemplates\Pages;

use App\Filament\Resources\EquipmentTaskChecklistTemplates\EquipmentTaskChecklistTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentTaskChecklistTemplate extends EditRecord
{
    protected static string $resource = EquipmentTaskChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
