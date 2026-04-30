<?php

namespace App\Filament\Resources\FuelTypes\Pages;

use App\Filament\Resources\FuelTypes\FuelTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelType extends ViewRecord
{
    protected static string $resource = FuelTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
