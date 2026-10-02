<?php

namespace App\Filament\Resources\AssetTypes\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\AssetTypes\AssetTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetType extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = AssetTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
