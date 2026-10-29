<?php

namespace App\Filament\Widgets;

use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Super admin, asset admin and asset manager: is the RFID side working? Scans coming in, when the last one arrived, tags in use,
 * and equipment that has no active tag (so it can never be scanned).
 */
class RfidHealthStats extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'RFID Health';

    /** A reader that has been quiet for this long is flagged. */
    private const QUIET_AFTER_HOURS = 24;

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager']) ?? false;
    }

    protected function getStats(): array
    {
        $figures = Cache::remember('dashboard.rfid_health', now()->addSeconds(60), fn (): array => $this->queryFigures());
        $lastScan = $figures['last_scan'];
        $lastScanAt = $lastScan === null ? null : Carbon::parse($lastScan);
        $quiet = $lastScanAt === null || $lastScanAt->lt(now()->subHours(self::QUIET_AFTER_HOURS));

        $withoutTag = $figures['without_tag'];

        return [
            Stat::make('Scans today', $figures['scans_today'])
                ->icon(Heroicon::Signal)
                ->description('Tag reads received since midnight')
                ->color('primary'),
            Stat::make('Last scan', $lastScanAt?->diffForHumans() ?? 'Never')
                ->icon(Heroicon::Clock)
                ->description($lastScanAt?->format('M d, Y h:i A') ?? 'No scans received yet')
                ->color($quiet ? 'danger' : 'success'),
            Stat::make('Active tags', $figures['active'])
                ->icon(Heroicon::Tag)
                ->description($figures['retired'].' retired')
                ->color('info'),
            Stat::make('Equipment without a tag', $withoutTag)
                ->icon(Heroicon::ExclamationTriangle)
                ->description($withoutTag > 0 ? 'Active equipment that cannot be scanned' : 'Every active equipment has a tag')
                ->color($withoutTag > 0 ? 'warning' : 'success'),
        ];
    }

    /**
     * The numbers behind the tiles. They come from tables that grow with every scan, so they are kept for a
     * minute instead of being counted again on every refresh.
     *
     * @return array{scans_today: int, last_scan: ?string, active: int, retired: int, without_tag: int}
     */
    private function queryFigures(): array
    {
        $tags = AssetTag::query()
            ->selectRaw('SUM(is_deleted = 0) as active, SUM(is_deleted = 1) as retired')
            ->toBase()
            ->first();

        return [
            'scans_today' => AssetTagLog::query()->where('detected_at', '>=', now()->startOfDay())->count(),
            'last_scan' => AssetTagLog::query()->max('detected_at'),
            'active' => (int) ($tags->active ?? 0),
            'retired' => (int) ($tags->retired ?? 0),
            'without_tag' => Equipment::query()
                ->equipmentAssets()
                ->where('eqm_is_active', true)
                ->whereDoesntHave('tags', fn ($tag) => $tag->where('is_deleted', false))
                ->count(),
        ];
    }
}
