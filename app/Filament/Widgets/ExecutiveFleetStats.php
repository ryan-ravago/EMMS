<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Executive committee: how big the fleet is and what it is doing right now. Read-only, no drill-down links.
 */
class ExecutiveFleetStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Fleet Overview';

    protected ?string $description = 'All registered assets and what they are doing.';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('execom') ?? false;
    }

    protected function getStats(): array
    {
        $row = Equipment::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(eqm_is_active = 1) as active')
            ->selectRaw("SUM(lifecycle_status_id = 'dep') as deployed")
            ->selectRaw("SUM(lifecycle_status_id = 'udmt') as maintenance")
            ->selectRaw("SUM(lifecycle_status_id = 'idle') as idle")
            ->toBase()
            ->first();

        $total = (int) ($row->total ?? 0);
        $share = fn (int $count): string => $total > 0 ? round($count / $total * 100).'% of all assets' : '—';

        return [
            Stat::make('Total assets', $total)
                ->icon(Heroicon::CubeTransparent)
                ->description((int) ($row->active ?? 0).' active, '.($total - (int) ($row->active ?? 0)).' inactive')
                ->color('primary'),
            Stat::make('Deployed', (int) ($row->deployed ?? 0))
                ->icon(Heroicon::Truck)
                ->description($share((int) ($row->deployed ?? 0)))
                ->color('success'),
            Stat::make('Under maintenance', (int) ($row->maintenance ?? 0))
                ->icon(Heroicon::WrenchScrewdriver)
                ->description($share((int) ($row->maintenance ?? 0)))
                ->color('warning'),
            Stat::make('Idle', (int) ($row->idle ?? 0))
                ->icon(Heroicon::PauseCircle)
                ->description($share((int) ($row->idle ?? 0)))
                ->color('gray'),
        ];
    }
}
