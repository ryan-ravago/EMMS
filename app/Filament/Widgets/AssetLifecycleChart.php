<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\LinkedHeading;
use App\Models\Equipment;
use App\Models\Status;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

/**
 * Super admin, asset admin, asset manager and the executive committee: where the assets are in their
 * lifecycle (deployed, idle, allocated, ...).
 */
class AssetLifecycleChart extends ChartWidget
{
    protected static ?int $sort = 5;

    /** The dashboard does not need live numbers: every refresh re-runs this widget's queries. */
    protected ?string $pollingInterval = '120s';

    /** Chart colours for the status colour names stored on statuses.status_color. */
    private const COLORS = [
        'success' => '#10b981',
        'info' => '#3b82f6',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
        'primary' => '#f59e0b',
        'gray' => '#9ca3af',
    ];

    public function getHeading(): string|HtmlString|null
    {
        return EquipmentResource::canViewPage('assets')
            ? new LinkedHeading('Assets by Lifecycle Status', route('filament.admin.resources.assets.index'))
            : 'Assets by Lifecycle Status';
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager', 'execom']) ?? false;
    }

    protected function getData(): array
    {
        $statuses = Status::query()->pluck('status_color', 'status_id');
        $titles = Status::query()->pluck('status_title', 'status_id');

        // Counted over every asset, so kept for a minute instead of being counted again on every refresh.
        $counts = collect(Cache::remember('dashboard.asset_lifecycle', now()->addSeconds(60), fn (): array => Equipment::query()
            ->selectRaw('lifecycle_status_id, COUNT(*) as aggregate')
            ->groupBy('lifecycle_status_id')
            ->toBase()
            ->get()
            ->map(fn (object $row): array => ['lifecycle_status_id' => $row->lifecycle_status_id, 'aggregate' => (int) $row->aggregate])
            ->all()))
            ->map(fn (array $row): object => (object) $row)
            ->sort(fn (object $a, object $b): int => [$b->aggregate, $a->lifecycle_status_id ?? '~'] <=> [$a->aggregate, $b->lifecycle_status_id ?? '~']);

        return [
            'datasets' => [
                [
                    'label' => 'Assets',
                    'data' => $counts->pluck('aggregate')->map(fn (mixed $count): int => (int) $count)->values()->all(),
                    'backgroundColor' => $counts
                        ->map(fn (object $row): string => self::COLORS[$statuses[$row->lifecycle_status_id] ?? 'gray'] ?? self::COLORS['gray'])
                        ->values()
                        ->all(),
                ],
            ],
            'labels' => $counts
                ->map(fn (object $row): string => $titles[$row->lifecycle_status_id] ?? 'No status')
                ->values()
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
