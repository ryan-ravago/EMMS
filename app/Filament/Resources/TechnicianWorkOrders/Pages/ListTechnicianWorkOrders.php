<?php

namespace App\Filament\Resources\TechnicianWorkOrders\Pages;

use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianWorkOrders extends ListRecords
{
    protected static string $resource = TechnicianWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
