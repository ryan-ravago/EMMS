<?php

namespace App\Filament\Resources\Equipment\Resources\WorkOrders\Pages;

use App\Filament\Resources\Equipment\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
