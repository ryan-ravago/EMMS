<?php

namespace App\Filament\Resources\Locations\Pages;

use Alareqi\FilamentTree\Concerns\InteractsWithTreeTable;
use App\Filament\Resources\Locations\LocationResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ListLocations extends ListRecords
{
    use InteractsWithTreeTable;

    protected static string $resource = LocationResource::class;

    // Tree icons: a place that contains other places gets a building, a place at the end of
    // a branch gets a map pin (instead of the plugin's folder and document icons).
    public function getTreeExpandedIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedBuildingOffice2;
    }

    public function getTreeCollapsedIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedBuildingOffice2;
    }

    public function getTreeLeafIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedMapPin;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
