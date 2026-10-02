<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\Technicians\TechnicianResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTechnician extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = TechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
