<?php

namespace App\Filament\Resources\InspectionItems\Pages;

use App\Filament\Resources\InspectionItems\InspectionItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInspectionItems extends ListRecords
{
    protected static string $resource = InspectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Inspection Item'),

            'pnd' => Tab::make('Pending Inspection Item')
                ->badge(fn () => InspectionItemResource::getEloquentQuery()->where('insi_status_id', 'pnd')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('insi_status_id', 'pnd')),

            'inprog' => Tab::make('Inspection Item In Progress')
                ->badge(fn () => InspectionItemResource::getEloquentQuery()->where('insi_status_id', 'inprog')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('insi_status_id', 'inprog')),

            'drg' => Tab::make('Disregarded Inspection Item')
                ->badge(fn () => InspectionItemResource::getEloquentQuery()->where('insi_status_id', 'drg')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('insi_status_id', 'drg')),

            'cmp' => Tab::make('Completed Inspection Item')
                ->badge(fn () => InspectionItemResource::getEloquentQuery()->where('insi_status_id', 'cmp')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('insi_status_id', 'cmp')),
        ];
    }
}
