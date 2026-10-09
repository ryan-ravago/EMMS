<?php

namespace App\Filament\Resources\AppUsers\Pages;

use App\Filament\Resources\AppUsers\AppUserResource;
use App\Filament\Support\UserDeleteActions;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAppUser extends EditRecord
{
    protected static string $resource = AppUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            UserDeleteActions::delete(),
        ];
    }
}
