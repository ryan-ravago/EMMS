<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags\Pages;

use App\Filament\Concerns\HasDetailsTab;
use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetTag extends ViewRecord
{
    use HasDetailsTab;
    use HasRecordNavigation;

    protected static string $resource = AssetTagResource::class;
}
