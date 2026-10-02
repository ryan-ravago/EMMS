<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\Locations\LocationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLocation extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
