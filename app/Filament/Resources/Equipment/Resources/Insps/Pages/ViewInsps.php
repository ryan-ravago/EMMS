<?php

namespace App\Filament\Resources\Equipment\Resources\Insps\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\Equipment\Resources\Insps\InspsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInsps extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = InspsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
