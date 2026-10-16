<?php

namespace App\Filament\Resources\WorkOrderTypes\Pages;

use App\Filament\Resources\WorkOrderTypes\WorkOrderTypeResource;
use App\Models\WorkOrderType;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrderType extends EditRecord
{
    protected static string $resource = WorkOrderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (WorkOrderType $record): bool => ! $record->workOrders()->exists())
                ->modalDescription('Only types that are not used by any work order can be deleted.'),
        ];
    }
}
