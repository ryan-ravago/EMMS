<?php

namespace App\Filament\Resources\AppUsers\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\AppUsers\AppUserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppUser extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = AppUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
