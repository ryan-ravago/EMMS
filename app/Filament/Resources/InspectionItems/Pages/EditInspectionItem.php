<?php

namespace App\Filament\Resources\InspectionItems\Pages;

use App\Filament\Resources\InspectionItems\InspectionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInspectionItem extends EditRecord
{
    protected static string $resource = InspectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
