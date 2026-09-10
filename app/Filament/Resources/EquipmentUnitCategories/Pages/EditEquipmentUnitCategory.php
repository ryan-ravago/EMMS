<?php

namespace App\Filament\Resources\EquipmentUnitCategories\Pages;

use App\Filament\Resources\EquipmentUnitCategories\EquipmentUnitCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentUnitCategory extends EditRecord
{
    protected static string $resource = EquipmentUnitCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
