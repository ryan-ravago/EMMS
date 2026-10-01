<?php

namespace App\Filament\Resources\AssetTags\Pages;

use App\Filament\Resources\AssetTags\AssetTagResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetTag extends ViewRecord
{
    protected static string $resource = AssetTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
