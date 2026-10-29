<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Executive committee: where the open work orders are piling up, per department.
 */
class OpenWorkOrdersByDepartmentChart extends ChartWidget
{
    protected static ?int $sort = 4;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    protected ?string $heading = 'Open Work Orders by Department';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('execom') ?? false;
    }

    protected function getData(): array
    {
        // Counted over every work order, so kept for a minute instead of being counted again on every refresh.
        $counts = collect(Cache::remember('dashboard.open_work_orders_by_department', now()->addSeconds(60), fn (): array => WorkOrder::query()
            ->whereIn('wo_status_id', WorkOrder::OPEN_STATUSES)
            ->selectRaw('wo_dep_id, COUNT(*) as aggregate')
            ->groupBy('wo_dep_id')
            ->toBase()
            ->get()
            ->map(fn (object $row): array => ['wo_dep_id' => $row->wo_dep_id === null ? null : (int) $row->wo_dep_id, 'aggregate' => (int) $row->aggregate])
            ->all()))
            ->map(fn (array $row): object => (object) $row)
            ->sort(fn (object $a, object $b): int => [$b->aggregate, (int) $a->wo_dep_id] <=> [$a->aggregate, (int) $b->wo_dep_id]);

        $names = Department::query()->pluck('dep_name', 'dep_id');

        return [
            'datasets' => [
                [
                    'label' => 'Open work orders',
                    'data' => $counts->pluck('aggregate')->map(fn (mixed $count): int => (int) $count)->values()->all(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $counts->map(fn (object $row): string => $names[$row->wo_dep_id] ?? 'No department')->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
