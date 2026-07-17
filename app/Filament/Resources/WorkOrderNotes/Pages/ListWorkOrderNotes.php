<?php

namespace App\Filament\Resources\WorkOrderNotes\Pages;

use App\Filament\Resources\WorkOrderNotes\WorkOrderNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderNotes extends ListRecords
{
    protected static string $resource = WorkOrderNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
