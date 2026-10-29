<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Models\AssetMovement;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Super admin, asset admin and asset manager: equipment trips (time out / time in) that nobody has verified
 * yet, each linking to its tab.
 */
class TripsNeedingAttentionStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Equipment Trips Needing Attention';

    protected ?string $description = 'Time out / time in records waiting for someone to verify them.';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null
            && $user->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager'])
            && $user->can('viewAny', AssetMovement::class);
    }

    protected function getStats(): array
    {
        $counts = AssetMovement::statusCounts();

        return [
            Stat::make('Pending acknowledgement', $counts['pending'])
                ->icon(Heroicon::ClipboardDocumentCheck)
                ->description('Both times recorded, waiting to be confirmed')
                ->color($counts['pending'] > 0 ? 'info' : 'success')
                ->url(AssetMovementResource::getUrl('index', ['tab' => 'pending'])),
            Stat::make('Missing time out', $counts['missing_out'])
                ->icon(Heroicon::ArrowRightStartOnRectangle)
                ->description('Came back, but the way out was not scanned')
                ->color($counts['missing_out'] > 0 ? 'warning' : 'success')
                ->url(AssetMovementResource::getUrl('index', ['tab' => 'missing_out'])),
            Stat::make('Missing time in', $counts['missing_in'])
                ->icon(Heroicon::ArrowLeftEndOnRectangle)
                ->description('Left, and the way back was not scanned')
                ->color($counts['missing_in'] > 0 ? 'warning' : 'success')
                ->url(AssetMovementResource::getUrl('index', ['tab' => 'missing_in'])),
        ];
    }
}
