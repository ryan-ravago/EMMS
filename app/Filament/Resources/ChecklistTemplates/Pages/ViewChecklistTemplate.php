<?php

namespace App\Filament\Resources\ChecklistTemplates\Pages;

use App\Filament\Resources\ChecklistTemplates\ChecklistTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChecklistTemplate extends ViewRecord
{
    protected static string $resource = ChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
