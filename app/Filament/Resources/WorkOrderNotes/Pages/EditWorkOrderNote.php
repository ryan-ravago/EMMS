<?php

namespace App\Filament\Resources\WorkOrderNotes\Pages;

use App\Filament\Resources\WorkOrderNotes\WorkOrderNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrderNote extends EditRecord
{
    protected static string $resource = WorkOrderNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
