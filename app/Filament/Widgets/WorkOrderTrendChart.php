<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class WorkOrderTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && ! $user->hasRole('asset_admin');
    }

    public function getHeading(): string|HtmlString|null
    {
        $user = auth()->user();
        $url = match (true) {
            $user->hasRole('technician') => TechnicianWorkOrderResource::getUrl(),
            $user->hasRole('requestor') => RequestorWorkOrderResource::getUrl(),
            default => WorkOrderResource::getUrl(),
        };

        return new HtmlString('<a href="'.$url.'" class="hover:underline transition">Work Order Trends</a>');
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $query = WorkOrder::query();

        if ($user->hasRole('manager')) {
            $query->where('wo_dep_id', $user->user_dep_id);
        } elseif ($user->hasRole('technician')) {
            $query->whereHas('workers', fn ($q) => $q->where('user_id', $user->user_id));
        } elseif ($user->hasRole('requestor')) {
            $query->where('wo_created_by', $user->user_id);
        }

        $data = $query
            ->select(
                DB::raw("DATE_FORMAT(wo_created_dt, '%Y-%m') as month"),
                DB::raw('count(*) as count')
            )
            ->where('wo_created_dt', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $chartData = array_merge($months, $data);

        return [
            'datasets' => [
                [
                    'label' => 'Work Orders Created',
                    'data' => array_values($chartData),
                    'fill' => 'start',
                ],
            ],
            'labels' => array_keys($chartData),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
