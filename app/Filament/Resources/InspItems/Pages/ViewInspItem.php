<?php

namespace App\Filament\Resources\InspItems\Pages;

use App\Filament\Resources\InspItems\InspItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInspItem extends ViewRecord
{
    protected static string $resource = InspItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
