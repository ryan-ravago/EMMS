<?php

namespace App\Filament\Widgets;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Executive committee: is maintenance work getting done? Open, waiting on a manager, finished this month,
 * and how long finished work orders took.
 */
class WorkOrderHealthStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Work Order Health';

    /** Finished work orders from this many days back are used for the average time to complete. */
    private const AVERAGE_OVER_DAYS = 90;

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('execom') ?? false;
    }

    protected function getStats(): array
    {
        // Counted over every work order, so kept for a minute instead of being counted again on every refresh.
        $figures = Cache::remember('dashboard.work_order_health', now()->addSeconds(60), fn (): array => [
            'open' => WorkOrder::query()->whereIn('wo_status_id', WorkOrder::OPEN_STATUSES)->count(),
            'awaiting' => WorkOrder::query()->whereIn('wo_status_id', WorkOrder::AWAITING_MANAGER_STATUSES)->count(),
            'completed_this_month' => WorkOrder::query()
                ->where('wo_status_id', 'cmp')
                ->where('wo_closed_dt', '>=', now()->startOfMonth())
                ->count(),
            'average' => $this->averageTimeToComplete(),
        ]);
        $open = $figures['open'];
        $awaiting = $figures['awaiting'];
        $completedThisMonth = $figures['completed_this_month'];

        return [
            Stat::make('Open work orders', $open)
                ->icon(Heroicon::DocumentCheck)
                ->description('Not yet completed')
                ->color('primary'),
            Stat::make('Waiting on a manager', $awaiting)
                ->icon(Heroicon::Clock)
                ->description($awaiting > 0 ? 'New requests and completion requests' : 'Nothing is waiting')
                ->color($awaiting > 0 ? 'warning' : 'success'),
            Stat::make('Completed this month', $completedThisMonth)
                ->icon(Heroicon::CheckBadge)
                ->description(now()->format('F Y'))
                ->color('success'),
            Stat::make('Average time to complete', $figures['average'])
                ->icon(Heroicon::Bolt)
                ->description('Work orders finished in the last '.self::AVERAGE_OVER_DAYS.' days')
                ->color('info'),
        ];
    }

    private function averageTimeToComplete(): string
    {
        $hours = WorkOrder::query()
            ->where('wo_status_id', 'cmp')
            ->whereNotNull('wo_closed_dt')
            ->where('wo_closed_dt', '>=', now()->subDays(self::AVERAGE_OVER_DAYS))
            ->get(['wo_created_dt', 'wo_closed_dt'])
            ->map(fn (WorkOrder $workOrder): float => Carbon::parse($workOrder->wo_created_dt)->diffInMinutes(Carbon::parse($workOrder->wo_closed_dt)) / 60);

        if ($hours->isEmpty()) {
            return '—';
        }

        $average = $hours->avg();

        return $average < 48
            ? round($average, 1).' hours'
            : round($average / 24, 1).' days';
    }
}
