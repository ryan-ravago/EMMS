<?php

namespace App\Filament\Resources\InspItems\Pages;

use App\Filament\Resources\InspItems\InspItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInspItems extends ListRecords
{
    protected static string $resource = InspItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
