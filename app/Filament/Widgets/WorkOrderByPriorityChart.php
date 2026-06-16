<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class WorkOrderByPriorityChart extends ChartWidget
{
    protected static ?int $sort = 5;

    public function getHeading(): string|HtmlString|null
    {
        $user = auth()->user();
        $url = match (true) {
            $user->hasRole('technician') => TechnicianWorkOrderResource::getUrl(),
            $user->hasRole('requestor') => RequestorWorkOrderResource::getUrl(),
            default => WorkOrderResource::getUrl(),
        };

        return new HtmlString('<a href="'.$url.'" class="hover:underline transition">Work Orders by Priority</a>');
    }

    protected function getData(): array
    {
        $query = WorkOrder::query()
            ->join('priorities', 'work_orders.wo_prio_id', '=', 'priorities.prio_id')
            ->select('priorities.prio_name', DB::raw('count(*) as count'))
            ->groupBy('priorities.prio_name');

        $user = auth()->user();
        if ($user->hasRole('requestor')) {
            $query->where('wo_created_by', $user->user_id);
        }

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Work Orders',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'], // red, amber, blue, green
                ],
            ],
            'labels' => $data->pluck('prio_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
