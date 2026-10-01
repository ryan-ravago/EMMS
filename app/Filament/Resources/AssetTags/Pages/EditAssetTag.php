<?php

namespace App\Filament\Resources\AssetTags\Pages;

use App\Filament\Resources\AssetTags\AssetTagResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetTag extends EditRecord
{
    protected static string $resource = AssetTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
