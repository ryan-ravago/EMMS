<?php

namespace App\Filament\Resources\AssetTagLogs\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\AssetTagLogs\AssetTagLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetTagLog extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = AssetTagLogResource::class;

    /**
     * Previous / Next follow the list, which is newest first: "Next" is the next row down, so the older scan.
     *
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    protected function getRecordNavigationOrder(): array
    {
        return ['id', 'desc'];
    }
}
