<?php

namespace App\Filament\Resources\EquipmentUnitCategories\Pages;

use App\Filament\Resources\EquipmentUnitCategories\EquipmentUnitCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentUnitCategories extends ListRecords
{
    protected static string $resource = EquipmentUnitCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
