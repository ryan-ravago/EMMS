<?php

namespace App\Filament\Resources\Insps\Pages;

use App\Filament\Resources\Insps\InspResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInsps extends ListRecords
{
    protected static string $resource = InspResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
