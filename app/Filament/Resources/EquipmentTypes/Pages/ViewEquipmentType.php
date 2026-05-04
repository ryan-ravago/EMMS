<?php

namespace App\Filament\Resources\EquipmentTypes\Pages;

use App\Filament\Resources\EquipmentTypes\EquipmentTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipmentType extends ViewRecord
{
    protected static string $resource = EquipmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
