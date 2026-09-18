<?php

namespace App\Filament\Resources\AdminManagers\Pages;

use App\Filament\Resources\AdminManagers\AdminManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdminManagers extends ListRecords
{
    protected static string $resource = AdminManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
