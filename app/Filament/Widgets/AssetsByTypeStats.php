<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AssetType;
use App\Models\Equipment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Super admin, asset admin, asset manager and the executive committee: how many assets of each type, from the
 * same cached counts as the sidebar badges. A tile only links to a list the person is allowed to open.
 */
class AssetsByTypeStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Assets by Type';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager', 'execom']) ?? false;
    }

    protected function getStats(): array
    {
        $counts = Equipment::assetCounts();
        $untyped = $counts['total'] - $counts[AssetType::EQUIPMENT] - $counts[AssetType::ACCESSORY] - $counts[AssetType::SERVICE_VEHICLE];

        return [
            Stat::make('Equipment', $counts[AssetType::EQUIPMENT])
                ->icon(Heroicon::Truck)
                ->color('primary')
                ->url($this->listUrl('equipment')),
            Stat::make('Accessories', $counts[AssetType::ACCESSORY])
                ->icon(Heroicon::Cog)
                ->color('success')
                ->url($this->listUrl('accessories')),
            Stat::make('Service Vehicles', $counts[AssetType::SERVICE_VEHICLE])
                ->icon(Heroicon::WrenchScrewdriver)
                ->color('info')
                ->url($this->listUrl('service-vehicles')),
            Stat::make('No asset type', $untyped)
                ->icon(Heroicon::QuestionMarkCircle)
                ->description($untyped > 0 ? 'Records that still need a type' : 'Every asset has a type')
                ->color($untyped > 0 ? 'warning' : 'success')
                ->url($untyped > 0 ? $this->listUrl('assets') : null),
        ];
    }

    /**
     * The asset list of one page key (equipment, accessories, service-vehicles, assets), or null when the
     * person may not open it.
     */
    private function listUrl(string $pageKey): ?string
    {
        return EquipmentResource::canViewPage($pageKey)
            ? route("filament.admin.resources.{$pageKey}.index")
            : null;
    }
}
