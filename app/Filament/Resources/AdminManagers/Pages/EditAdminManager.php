<?php

namespace App\Filament\Resources\AdminManagers\Pages;

use App\Filament\Resources\AdminManagers\AdminManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminManager extends EditRecord
{
    protected static string $resource = AdminManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
