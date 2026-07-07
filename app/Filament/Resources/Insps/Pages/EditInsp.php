<?php

namespace App\Filament\Resources\Insps\Pages;

use App\Filament\Resources\Insps\InspResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInsp extends EditRecord
{
    protected static string $resource = InspResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
