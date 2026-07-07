<?php

namespace App\Filament\Resources\RequestorWorkOrders\Pages;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRequestorWorkOrders extends ListRecords
{
    protected static string $resource = RequestorWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(auth()->user()->hasRole('super_admin') ? 'New Requestor Work Order' : 'New Work Order'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label('All')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            'pndwor' => Tab::make()
                ->label('Pending WO Review')
                ->badge(fn () => RequestorWorkOrderResource::getEloquentQuery()->where('wo_status_id', 'pndwor')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'pndwor')),
            // 'approved' => Tab::make()
            //     ->label('Approved')
            //     ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'inprog')),
            // 'rej' => Tab::make()
            //     ->label('Rejected')
            //     ->modifyQueryUsing(fn(Builder $query) => $query->where('wo_status_id', 'rej')),
            'inprog' => Tab::make('In Progress')
                ->badge(fn () => RequestorWorkOrderResource::getEloquentQuery()->where('wo_status_id', 'inprog')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'inprog')),

            'rej' => Tab::make('Rejected')
                ->badge(fn () => RequestorWorkOrderResource::getEloquentQuery()->where('wo_status_id', 'rej')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'rej')),

            'cnc' => Tab::make('Cancelled')
                ->badge(fn () => RequestorWorkOrderResource::getEloquentQuery()->where('wo_status_id', 'cnc')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'cnc')),

            'cmp' => Tab::make('Completed')
                ->badge(fn () => RequestorWorkOrderResource::getEloquentQuery()->where('wo_status_id', 'cmp')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'cmp')),
        ];
    }
}
