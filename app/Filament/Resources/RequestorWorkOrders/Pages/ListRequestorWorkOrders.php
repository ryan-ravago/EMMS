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
            'pnd' => Tab::make()
                ->label('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'pnd')),
            'approved' => Tab::make()
                ->label('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'inprog')),
            'rej' => Tab::make()
                ->label('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_status_id', 'rej')),
        ];
    }
}
