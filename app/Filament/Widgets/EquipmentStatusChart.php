<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class EquipmentStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.EquipmentResource::getUrl().'" class="hover:underline transition">Asset Status Distribution</a>');
    }

    protected function getData(): array
    {
        $active = Equipment::where('eqm_is_active', true)->count();
        $inactive = Equipment::where('eqm_is_active', false)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Equipment Status',
                    'data' => [$active, $inactive],
                    'backgroundColor' => ['#10b981', '#ef4444'], // green-500, red-500
                ],
            ],
            'labels' => ['Active', 'Inactive'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
