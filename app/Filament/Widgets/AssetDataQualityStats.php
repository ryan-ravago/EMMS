<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Super admin and asset admin: active assets with gaps in their data, the records an asset admin has to fix.
 * (Assets with no type and equipment with no tag are shown by the "Assets by Type" and "RFID Health" sections.)
 */
class AssetDataQualityStats extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected ?string $heading = 'Asset Data to Complete';

    protected ?string $description = 'Active assets that are missing information.';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'asset_admin']) ?? false;
    }

    protected function getStats(): array
    {
        $active = fn () => Equipment::query()->where('eqm_is_active', true);

        // An accessory allocated to equipment sits where its parent is, so it needs no location of its own.
        $withoutLocation = $active()->whereNull('location_id')->whereNull('parent_id')->count();
        $withoutModel = $active()->whereNull('eqm_eqmm_id')->count();
        $withoutCategory = $active()->whereDoesntHave('categories')->count();

        return [
            $this->gap('No location', $withoutLocation, Heroicon::MapPin, 'Not allocated and no location set'),
            $this->gap('No model', $withoutModel, Heroicon::Squares2x2, 'No equipment model chosen'),
            $this->gap('No category', $withoutCategory, Heroicon::Square3Stack3d, 'Not in any category'),
        ];
    }

    private function gap(string $label, int $count, Heroicon $icon, string $description): Stat
    {
        return Stat::make($label, $count)
            ->icon($icon)
            ->description($count > 0 ? $description : 'Nothing missing')
            ->color($count > 0 ? 'warning' : 'success');
    }
}
