<?php

namespace App\Filament\Resources\Locations\Pages;

use Alareqi\FilamentTree\Concerns\InteractsWithTreeTable;
use App\Filament\Resources\Locations\LocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocations extends ListRecords
{
    use InteractsWithTreeTable;

    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
