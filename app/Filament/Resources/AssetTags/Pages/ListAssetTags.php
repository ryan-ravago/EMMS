<?php

namespace App\Filament\Resources\AssetTags\Pages;

use App\Filament\Resources\AssetTags\AssetTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetTags extends ListRecords
{
    protected static string $resource = AssetTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
