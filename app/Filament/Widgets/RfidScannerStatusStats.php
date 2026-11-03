<?php

namespace App\Filament\Widgets;

use App\Models\RfidScannerStatus;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Super admin, asset admin and asset manager: is each gate scanner working? One tile per scanner, as relayed by the
 * reader server. Nothing is shown until the first report arrives.
 */
class RfidScannerStatusStats extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Gate Scanners';

    public static function canView(): bool
    {
        return (Auth::user()?->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager']) ?? false)
            && RfidScannerStatus::query()->exists();
    }

    protected function getStats(): array
    {
        $scanners = Cache::remember('dashboard.rfid_scanners', now()->addSeconds(60), fn (): array => $this->queryScanners());

        return array_map(function (array $scanner): Stat {
            $since = Carbon::parse($scanner['since'])->format('M d, h:i A');

            return Stat::make($scanner['label'], RfidScannerStatus::stateLabels()[$scanner['state']])
                ->icon(Heroicon::Signal)
                ->description($scanner['state'] === RfidScannerStatus::NO_REPORT
                    ? 'No report for '.$scanner['minutes'].' min · last status since '.$since
                    : 'Since '.$since)
                ->color(RfidScannerStatus::stateColor($scanner['state']));
        }, $scanners);
    }

    /**
     * @return list<array{label: string, state: string, since: string, minutes: int}>
     */
    private function queryScanners(): array
    {
        return RfidScannerStatus::query()
            ->with('yard')
            ->orderBy('yard_id')
            ->orderBy('scanner_name')
            ->get()
            ->map(fn (RfidScannerStatus $scanner): array => [
                'label' => $scanner->label(),
                'state' => $scanner->state(),
                'since' => $scanner->status_changed_at->toDateTimeString(),
                'minutes' => $scanner->minutesSinceLastReport(),
            ])
            ->all();
    }
}
