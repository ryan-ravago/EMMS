<?php

namespace App\Filament\Resources\WorkOrderTypes\Pages;

use App\Filament\Resources\WorkOrderTypes\WorkOrderTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderTypes extends ListRecords
{
    protected static string $resource = WorkOrderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
