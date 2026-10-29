<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Super admin and asset admin: active assets with gaps in their data, the records an asset admin has to fix.
 * (Assets with no type and equipment with no tag are shown by the "Assets by Type" and "RFID Health" sections.)
 */
class AssetDataQualityStats extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Asset Data to Complete';

    protected ?string $description = 'Active assets that are missing information.';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'asset_admin']) ?? false;
    }

    protected function getStats(): array
    {
        $gaps = Cache::remember('dashboard.asset_data_gaps', now()->addSeconds(60), fn (): array => $this->queryGaps());

        return [
            $this->gap('No location', $gaps['location'], Heroicon::MapPin, 'Not allocated and no location set'),
            $this->gap('No model', $gaps['model'], Heroicon::Squares2x2, 'No equipment model chosen'),
            $this->gap('No category', $gaps['category'], Heroicon::Square3Stack3d, 'Not in any category'),
        ];
    }

    /**
     * Counted over every asset, so kept for a minute instead of being counted again on every refresh.
     *
     * @return array{location: int, model: int, category: int}
     */
    private function queryGaps(): array
    {
        $active = fn () => Equipment::query()->where('eqm_is_active', true);

        return [
            // An accessory allocated to equipment sits where its parent is, so it needs no location of its own.
            'location' => $active()->whereNull('location_id')->whereNull('parent_id')->count(),
            'model' => $active()->whereNull('eqm_eqmm_id')->count(),
            'category' => $active()->whereDoesntHave('categories')->count(),
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
