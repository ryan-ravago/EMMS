<?php

namespace App\Filament\Resources\Equipment\Resources\Insps\Pages;

use App\Filament\Resources\Equipment\Resources\Insps\InspsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInsps extends EditRecord
{
    protected static string $resource = InspsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
