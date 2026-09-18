<?php

namespace App\Filament\Resources\AdminManagers\Pages;

use App\Filament\Resources\AdminManagers\AdminManagerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdminManager extends ViewRecord
{
    protected static string $resource = AdminManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
