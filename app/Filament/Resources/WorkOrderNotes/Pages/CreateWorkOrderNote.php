<?php

namespace App\Filament\Resources\WorkOrderNotes\Pages;

use App\Filament\Resources\WorkOrderNotes\WorkOrderNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrderNote extends CreateRecord
{
    protected static string $resource = WorkOrderNoteResource::class;
}
