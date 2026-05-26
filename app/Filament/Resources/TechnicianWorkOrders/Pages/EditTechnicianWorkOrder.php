<?php

namespace App\Filament\Resources\TechnicianWorkOrders\Pages;

use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTechnicianWorkOrder extends EditRecord
{
    protected static string $resource = TechnicianWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
