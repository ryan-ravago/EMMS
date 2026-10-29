<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

/**
 * Executive committee: where the open work orders are piling up, per department.
 */
class OpenWorkOrdersByDepartmentChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Open Work Orders by Department';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('execom') ?? false;
    }

    protected function getData(): array
    {
        $counts = WorkOrder::query()
            ->whereIn('wo_status_id', WorkOrder::OPEN_STATUSES)
            ->selectRaw('wo_dep_id, COUNT(*) as aggregate')
            ->groupBy('wo_dep_id')
            ->toBase()
            ->get()
            ->sort(fn (object $a, object $b): int => [(int) $b->aggregate, (int) $a->wo_dep_id] <=> [(int) $a->aggregate, (int) $b->wo_dep_id]);

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
