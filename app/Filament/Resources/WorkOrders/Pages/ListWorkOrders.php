<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getBaseQuery(): Builder
    {
        $query = WorkOrder::query();

        if (! Auth::user()?->hasRole('super_admin')) {
            $query->where('wo_dep_id', Auth::user()?->user_dep_id);
        }

        return $query;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn() => $this->getBaseQuery()->count()),

            'inprog' => Tab::make('In Progress')
                ->badge(fn() => $this->getBaseQuery()->where('wo_status_id', 'inprog')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'inprog')),

            'pca' => Tab::make('Pending Completion Approval')
                ->badge(fn() => $this->getBaseQuery()->where('wo_status_id', 'pca')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'pca')),

            'rej' => Tab::make('Rejected')
                ->badge(fn() => $this->getBaseQuery()->where('wo_status_id', 'rej')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'rej')),

            'cnc' => Tab::make('Cancelled')
                ->badge(fn() => $this->getBaseQuery()->where('wo_status_id', 'cnc')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'cnc')),

            'cmp' => Tab::make('Completed')
                ->badge(fn() => $this->getBaseQuery()->where('wo_status_id', 'cmp')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'cmp')),
        ];
    }
}
