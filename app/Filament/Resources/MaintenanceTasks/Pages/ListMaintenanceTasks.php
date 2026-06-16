<?php

namespace App\Filament\Resources\MaintenanceTasks\Pages;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\MaintenanceTask;
use App\Models\Status;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMaintenanceTasks extends ListRecords
{
    protected static string $resource = MaintenanceTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Tasks')
                ->badge(MaintenanceTask::query()->count()),
        ];

        $statusIds = ['pnd', 'inprog', 'snz', 'cmp'];
        $statuses = Status::whereIn('status_id', $statusIds)
            ->get()
            ->sortBy(fn ($status) => array_search($status->status_id, $statusIds));

        foreach ($statuses as $status) {
            $tabs[$status->status_id] = Tab::make($status->status_title)
                ->icon($status->status_icon)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mt_status_id', $status->status_id))
                ->badge(MaintenanceTask::query()->where('mt_status_id', $status->status_id)->count());
        }

        return $tabs;
    }
}
