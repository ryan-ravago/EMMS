<?php

namespace App\Filament\Resources\WorkOrderTypes\Pages;

use App\Filament\Resources\WorkOrderTypes\WorkOrderTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkOrderType extends ViewRecord
{
    protected static string $resource = WorkOrderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
