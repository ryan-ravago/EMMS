<?php

namespace App\Filament\Resources\AssetTagLogs;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\RfidTags\RfidTagResource;
use App\Models\AssetTagLog;
use Illuminate\Support\Facades\Auth;

/**
 * Where a scan's tag and asset (its parents) can be opened. A link is only given when the person may open that
 * page, so nobody is sent to a page that answers "forbidden".
 */
class AssetTagLogLinks
{
    /**
     * The RFID tag's page, or null when the tag is unknown or the viewer may not open it.
     */
    public static function tag(AssetTagLog $log): ?string
    {
        if ($log->tag === null || ! Auth::user()?->can('view', $log->tag)) {
            return null;
        }

        return RfidTagResource::getUrl('view', ['record' => $log->tag]);
    }

    /**
     * The asset's page (equipment, service vehicle or accessory), or null when there is none or the viewer may not open it.
     */
    public static function asset(AssetTagLog $log): ?string
    {
        $asset = $log->tag?->asset;

        if ($asset === null || ! Auth::user()?->can('view', $asset)) {
            return null;
        }

        return EquipmentResource::getUrl('view', ['record' => $asset], configuration: EquipmentResource::pageKeyForRecord($asset));
    }
}
