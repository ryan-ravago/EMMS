<?php

namespace App\Filament\Resources\InspItems\Pages;

use App\Filament\Resources\InspItems\InspItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInspItem extends EditRecord
{
    protected static string $resource = InspItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
