<?php

namespace App\Filament\Resources\Insps\Pages;

use App\Filament\Resources\Insps\InspResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInsp extends ViewRecord
{
    protected static string $resource = InspResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
